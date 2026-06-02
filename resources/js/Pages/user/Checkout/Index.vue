<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Truck, Wallet, CheckCircle2, ShieldCheck, Clock3, X, QrCode, Download, MapPin, Navigation, Loader2, Wifi } from 'lucide-vue-next';
import QRCode from 'qrcode';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';
import DeliveryLocationMap from '@/Components/User/DeliveryLocationMap.vue';
import { useStore } from '@/composables/useStore';

const { cart, cartSubtotal, cartDiscount, cartTotal, clearCart, ensureCartLoaded } = useStore();
const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);
const errors = computed(() => page.props.errors ?? {});
const delivery = computed(() => page.props.delivery ?? {});
const savedAddress = computed(() => page.props.savedAddress ?? null);
const storeSettings = computed(() => page.props.store ?? page.props.appSettings ?? {});

function parseMapCoordinate(value, fallback) {
    const parsed = Number(String(value ?? '').trim());

    return Number.isFinite(parsed) ? parsed : fallback;
}

function normalizeStoreCoordinates(lat, lng) {
    let normalizedLat = parseMapCoordinate(lat, 11.5564);
    let normalizedLng = parseMapCoordinate(lng, 104.9282);

    // Fix common swapped lat/long entries (e.g. lat=104, lng=11 for Cambodia).
    if (Math.abs(normalizedLat) > 50 && Math.abs(normalizedLng) < 50) {
        return { lat: normalizedLng, lng: normalizedLat };
    }

    return { lat: normalizedLat, lng: normalizedLng };
}

const storeCoords = computed(() =>
    normalizeStoreCoordinates(storeSettings.value.map_lat, storeSettings.value.map_long),
);
const storeLat = computed(() => storeCoords.value.lat);
const storeLng = computed(() => storeCoords.value.lng);
const storeName = computed(() => storeSettings.value.name ?? page.props.appSettings?.store_name ?? 'Store');
const storeAddress = computed(() => storeSettings.value.address ?? page.props.appSettings?.address ?? '');
const storeMapLabel = computed(() => {
    if (storeName.value && storeAddress.value) {
        return `${storeName.value} — ${storeAddress.value}`;
    }

    return storeName.value || storeAddress.value || 'Store';
});

const addressSource = ref(null);

onMounted(() => {
    ensureCartLoaded();

    if (authUser.value) {
        form.value.name = authUser.value.name ?? form.value.name;
        form.value.phone = authUser.value.phone ?? form.value.phone;
        form.value.email = authUser.value.email ?? form.value.email;
    }

    if (savedAddress.value) {
        form.value.address = savedAddress.value.address ?? form.value.address;
        form.value.floor = savedAddress.value.floor ?? form.value.floor;

        if (savedAddress.value.qty_kilo) {
            form.value.qty_kilo = Number(savedAddress.value.qty_kilo);
        }

        addressSource.value = 'saved';
        locationHint.value =
            'Your last delivery address is shown below — it may not be where you are now. Tap GPS location for your current position, or edit the address.';
    }

    showAddressMap.value = true;

    if (checkoutResult.value) {
        showOrderSuccessModal.value = true;
    }

    void renderBakongQr();
});

const method = ref('aba');
const placing = ref(false);
const showKhqrModal = ref(false);
const paymentExpirySeconds = ref(5 * 60);
const paymentTimer = ref(null);
const paymentSuccessTimer = ref(null);
const bakongQrDataUrl = ref('');
const bakongQrSvg = ref('');
const staticBakongQrPayload = '00020101021115311974011600520446BONG1000231208129180014yem_davit@bkrt5204599953031165802KH5909DAVIT YEM6010Phnom Penh6304689E';
const paymentSuccessDelayMs = 15000;

const methods = [
    // { id: 'card', name: 'Credit / Debit card', desc: 'Visa, Mastercard, JCB', icon: CreditCard },
    { id: 'aba', name: 'Online Pay', desc: 'Scan with the ABA mobile app', icon: Wallet },
    { id: 'cod', name: 'Cash on delivery', desc: 'Pay when you receive your order', icon: Truck },
];

const total = computed(() => cartTotal.value);
const qrAmount = computed(() => {
    return placeOrderTotal.value.toFixed(2);
});
const checkoutResult = computed(() => page.props.flash?.checkout ?? null);
const showOrderSuccessModal = ref(false);
const orderSuccessDescription = computed(() => {
    if (!checkoutResult.value) {
        return '';
    }

    if (checkoutResult.value.payment_method === 'online') {
        return `Order ${checkoutResult.value.order_number} placed successfully. Payment confirmed.`;
    }

    return `Order ${checkoutResult.value.order_number} placed. Pay cash on delivery when your order arrives.`;
});

watch(checkoutResult, (result) => {
    if (result) {
        showOrderSuccessModal.value = true;
        showKhqrModal.value = false;
    }
});

const expiryLabel = computed(() => formatDuration(paymentExpirySeconds.value));
const deliveryFeePerKg = computed(() => Number(delivery.value?.fee_amount_per ?? 0));
const addressQtyKg = computed(() => Number(form.value.qty_kilo || 1));
const cartDeliveryFee = computed(() => deliveryFeePerKg.value * addressQtyKg.value);
const deliveryFormulaLabel = computed(() => `Delivery $${deliveryFeePerKg.value.toFixed(2)} × ${addressQtyKg.value.toFixed(2)} KM`);
const placeOrderTotal = computed(() => Math.max(0, total.value + couponDeliveryFee.value - couponDiscount.value));
const checkoutDeliveryFee = computed(() => Number(checkoutResult.value?.delivery_fee ?? 0));
const checkoutDiscountType = computed(() => checkoutResult.value?.discount_type ?? '—');
const checkoutDiscountValue = computed(() => Number(checkoutResult.value?.discount_value ?? 0));
const checkoutCouponCode = computed(() => checkoutResult.value?.coupon_code ?? '');
const checkoutCouponType = computed(() => checkoutResult.value?.coupon_type ?? '—');
const checkoutCouponValue = computed(() => Number(checkoutResult.value?.coupon_value ?? 0));
const checkoutCouponDiscount = computed(() => Number(checkoutResult.value?.coupon_discount ?? 0));
const appliedCoupon = ref(null);
const couponSaving = ref(false);
const couponError = ref('');
const couponDiscount = computed(() => Number(appliedCoupon.value?.coupon_discount ?? 0));
const couponDeliveryFee = computed(() => Number(appliedCoupon.value?.delivery_fee ?? cartDeliveryFee.value));

const form = ref({
    name: '',
    phone: '',
    email: '',
    address: '',
    floor: '',
    qty_kilo: 1,
    coupon_code: '',
    note: '',
});
const qtyKiloTouched = ref(false);

watch(
    () => form.value.coupon_code,
    (value) => {
        if (appliedCoupon.value && value !== appliedCoupon.value.coupon_code) {
            appliedCoupon.value = null;
            couponError.value = '';
        }
    }
);

watch(
    () => form.value.qty_kilo,
    () => {
        if (appliedCoupon.value) {
            appliedCoupon.value = null;
            couponError.value = '';
        }
    }
);

const showAddressMap = ref(false);
const deliveryLat = ref(null);
const deliveryLng = ref(null);
const mapLoading = ref(false);
const mapError = ref('');
const locationHint = ref('');
const geolocationBlocked = ref(false);
let geocodeTimer = null;

const deliveryDistanceKm = computed(() => {
    if (deliveryLat.value == null || deliveryLng.value == null) {
        return null;
    }

    return haversineKm(storeLat.value, storeLng.value, deliveryLat.value, deliveryLng.value);
});

const hasDeliveryLocation = computed(
    () => deliveryLat.value != null && deliveryLng.value != null,
);

const deliveryLocationLabel = computed(() => {
    if (form.value.address.trim()) {
        return form.value.address.trim();
    }

    if (!hasDeliveryLocation.value) {
        return '';
    }

    return `${deliveryLat.value.toFixed(5)}, ${deliveryLng.value.toFixed(5)}`;
});

watch(deliveryDistanceKm, (distance) => {
    if (distance == null || qtyKiloTouched.value) {
        return;
    }

    form.value.qty_kilo = Number(distance.toFixed(3));
});

const googleMapsUrl = computed(() => {
    if (!hasDeliveryLocation.value) {
        return '';
    }

    const saddr = `${storeLat.value},${storeLng.value}`;
    const daddr = `${deliveryLat.value},${deliveryLng.value}`;

    return `https://www.google.com/maps/dir/?api=1&origin=${encodeURIComponent(saddr)}&destination=${encodeURIComponent(daddr)}`;
});

function haversineKm(lat1, lon1, lat2, lon2) {
    const toRad = (deg) => (deg * Math.PI) / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;

    return Math.round(6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)) * 10) / 10;
}

async function geocodeAddress(address) {
    const query = address?.trim();

    if (!query || query.length < 3) {
        return false;
    }

    try {
        const axios = (await import('axios')).default;
        const response = await axios.get(route('checkout.geocode'), {
            params: { q: query },
            headers: { Accept: 'application/json' },
        });

        deliveryLat.value = Number(response.data.lat);
        deliveryLng.value = Number(response.data.lng);

        if (response.data.display_name) {
            form.value.address = response.data.display_name;
        }

        addressSource.value = 'typed';

        return true;
    } catch (error) {
        const message = error?.response?.data?.message;

        if (message) {
            throw new Error(message);
        }

        return false;
    }
}

function getGeolocationPosition(options) {
    return new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, options);
    });
}

async function refreshGeolocationPermission() {
    if (!navigator.permissions?.query) {
        return 'unknown';
    }

    try {
        const status = await navigator.permissions.query({ name: 'geolocation' });
        geolocationBlocked.value = status.state === 'denied';
        return status.state;
    } catch {
        return 'unknown';
    }
}

function applyGpsPosition(position) {
    deliveryLat.value = position.coords.latitude;
    deliveryLng.value = position.coords.longitude;
    geolocationBlocked.value = false;
    addressSource.value = 'gps';
    locationHint.value = '';
    void fillAddressFromCoordinates(true);
}

function handleGeolocationFailure(error) {
    mapError.value = '';

    if (error?.code === 1) {
        geolocationBlocked.value = true;
        locationHint.value =
            'GPS is blocked for this site. Use the steps below, try “Network location”, tap the map, or type your address.';
        return;
    }

    if (error?.code === 3) {
        locationHint.value =
            'GPS timed out. Try again, use “Network location”, tap the map, or type your address.';
        return;
    }

    locationHint.value =
        'Could not read GPS. Try “Network location”, tap the map, or type your address.';
}

async function useCurrentLocation() {
    if (!navigator.geolocation) {
        locationHint.value =
            'GPS is not supported in this browser. Use “Network location”, tap the map, or type your address.';
        return;
    }

    if (!window.isSecureContext) {
        locationHint.value =
            'GPS needs HTTPS (or localhost). Use “Network location”, tap the map, or type your address.';
        return;
    }

    const permission = await refreshGeolocationPermission();

    if (permission === 'denied') {
        locationHint.value =
            'GPS is blocked for this site. Use the steps below, try “Network location”, or tap the map.';
        return;
    }

    mapLoading.value = true;
    mapError.value = '';
    locationHint.value = permission === 'prompt' ? 'Allow location when your browser asks.' : '';

    const attempts = [
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 },
        { enableHighAccuracy: false, timeout: 20000, maximumAge: 120000 },
    ];

    try {
        let lastError = null;

        for (const options of attempts) {
            try {
                const position = await getGeolocationPosition(options);
                applyGpsPosition(position);
                return;
            } catch (error) {
                lastError = error;
                if (error?.code === 1) {
                    break;
                }
            }
        }

        handleGeolocationFailure(lastError);

        if (form.value.address.trim().length >= 3) {
            void resolveDeliveryFromAddress();
        }
    } finally {
        mapLoading.value = false;
    }
}

async function useNetworkLocation() {
    mapLoading.value = true;
    mapError.value = '';
    locationHint.value = 'Detecting approximate location from your network…';

    try {
        const response = await fetch('https://get.geojs.io/v1/ip/geo.json');

        if (!response.ok) {
            throw new Error('Network location unavailable');
        }

        const data = await response.json();
        const lat = Number(data.latitude);
        const lng = Number(data.longitude);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            throw new Error('Invalid coordinates');
        }

        deliveryLat.value = lat;
        deliveryLng.value = lng;
        geolocationBlocked.value = false;
        addressSource.value = 'network';
        locationHint.value =
            'Network location is only approximate (often the wrong street). Enable GPS location for your real position, or drag the pin on the map.';
        await fillAddressFromCoordinates(true);
    } catch {
        mapError.value =
            'Could not detect network location. Tap the map to set your pin, enable GPS in browser settings, or type your address.';
        locationHint.value = '';
    } finally {
        mapLoading.value = false;
    }
}

function onMapLocationPick({ lat, lng }) {
    deliveryLat.value = lat;
    deliveryLng.value = lng;
    mapError.value = '';
    geolocationBlocked.value = false;
    addressSource.value = 'map';
    locationHint.value = '';
    void fillAddressFromCoordinates(true);
}

function formatCoordinateAddress(lat, lng) {
    return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
}

async function fillAddressFromCoordinates(force = false) {
    if (deliveryLat.value == null || deliveryLng.value == null) {
        return;
    }

    if (!force && form.value.address.trim()) {
        return;
    }

    const lat = deliveryLat.value;
    const lng = deliveryLng.value;

    try {
        const axios = (await import('axios')).default;
        const response = await axios.get(route('checkout.reverse-geocode'), {
            params: { lat, lng },
            headers: { Accept: 'application/json' },
        });

        if (response.data?.display_name) {
            form.value.address = response.data.display_name;
            return;
        }
    } catch {
        // Fall through to coordinate fallback.
    }

    form.value.address = formatCoordinateAddress(lat, lng);

    if (addressSource.value === 'gps') {
        locationHint.value = 'Street name not found for this GPS point. Edit the address text or drag the pin on the map.';
    } else if (addressSource.value === 'network') {
        locationHint.value =
            'Network location could not be converted to a street address. Enable GPS, drag the pin, or type your address.';
    } else {
        locationHint.value = 'Edit the delivery address above to match your exact location.';
    }
}

async function resolveDeliveryFromAddress() {
    const query = form.value.address?.trim();

    if (!query || query.length < 3) {
        if (!hasDeliveryLocation.value) {
            deliveryLat.value = null;
            deliveryLng.value = null;
        }

        if (!query) {
            mapError.value = '';
            locationHint.value = '';
        }

        return;
    }

    mapLoading.value = true;
    mapError.value = '';

    try {
        const found = await geocodeAddress(query);

        if (!found) {
            deliveryLat.value = null;
            deliveryLng.value = null;
            mapError.value = 'Could not find this address on the map. Include street/area in Phnom Penh, or tap “Use my location”.';
        } else {
            if (addressSource.value !== 'saved') {
                locationHint.value = '';
            }
        }
    } catch (error) {
        deliveryLat.value = null;
        deliveryLng.value = null;
        mapError.value = error?.message || 'Could not load the map for this address.';
    } finally {
        mapLoading.value = false;
    }
}

function confirmAddressOnMap() {
    if (form.value.address.trim().length >= 3) {
        addressSource.value = addressSource.value === 'saved' ? 'saved' : 'typed';
        void resolveDeliveryFromAddress();
    }
}

function openAddressMap() {
    showAddressMap.value = true;
    void refreshGeolocationPermission();
}

watch(
    () => form.value.address,
    (address) => {
        if (!showAddressMap.value) {
            return;
        }

        if (geocodeTimer) {
            window.clearTimeout(geocodeTimer);
        }

        geocodeTimer = window.setTimeout(() => {
            if (addressSource.value === 'saved') {
                addressSource.value = 'typed';
            }

            void resolveDeliveryFromAddress();
        }, 700);
    }
);

function startPaymentTimer() {
    clearPaymentTimer();
    paymentExpirySeconds.value = 5 * 60;

    paymentTimer.value = window.setInterval(() => {
        if (paymentExpirySeconds.value <= 1) {
            clearPaymentTimer();
            paymentExpirySeconds.value = 0;
            return;
        }

        paymentExpirySeconds.value -= 1;
    }, 1000);
}

function clearPaymentTimer() {
    if (paymentTimer.value) {
        window.clearInterval(paymentTimer.value);
        paymentTimer.value = null;
    }
}

function schedulePaymentSuccessPopup() {
    clearPaymentSuccessTimer();

    paymentSuccessTimer.value = window.setTimeout(() => {
        showKhqrModal.value = false;
        paymentSuccessTimer.value = null;
        submitOrder('online');
    }, paymentSuccessDelayMs);
}

function clearPaymentSuccessTimer() {
    if (paymentSuccessTimer.value) {
        window.clearTimeout(paymentSuccessTimer.value);
        paymentSuccessTimer.value = null;
    }
}

function openKhqrModal() {
    showKhqrModal.value = true;
    startPaymentTimer();
    schedulePaymentSuccessPopup();
    void renderBakongQr();
}

function closeKhqrModal() {
    showKhqrModal.value = false;
    clearPaymentTimer();
    clearPaymentSuccessTimer();
}

async function placeOrder() {
    if (method.value === 'aba') {
        openKhqrModal();
        return;
    }

    submitOrder('cash');
}

function confirmOnlinePayment() {
    clearPaymentSuccessTimer();
    submitOrder('online');
}

function submitOrder(payment_method) {
    placing.value = true;

    router.post(
        route('checkout.store'),
        {
            name: form.value.name,
            phone: form.value.phone,
            address: form.value.address,
            floor: form.value.floor || null,
            qty_kilo: form.value.qty_kilo || 1,
            coupon_code: appliedCoupon.value?.coupon_code || form.value.coupon_code || null,
            payment_method,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                placing.value = false;
            },
            onSuccess: () => {
                clearCart();
                showKhqrModal.value = false;
                clearPaymentTimer();
                clearPaymentSuccessTimer();
            },
            onError: () => {
                showKhqrModal.value = false;
                clearPaymentTimer();
                clearPaymentSuccessTimer();
            },
        }
    );
}

function closeOrderSuccessModal() {
    showOrderSuccessModal.value = false;
}

async function applyCoupon() {
    couponSaving.value = true;
    couponError.value = '';

    try {
        const axios = (await import('axios')).default;
        const res = await axios.post(route('checkout.apply-coupon'), {
            coupon_code: form.value.coupon_code,
            qty_kilo: form.value.qty_kilo || 1,
        }, {
            headers: { Accept: 'application/json' },
        });

        form.value.coupon_code = res.data.coupon_code || form.value.coupon_code;
        appliedCoupon.value = res.data;
    } catch (error) {
        appliedCoupon.value = null;
        couponError.value = error?.response?.data?.message ?? 'Invalid coupon code.';
    } finally {
        couponSaving.value = false;
    }
}

function clearCoupon() {
    appliedCoupon.value = null;
    couponError.value = '';
    form.value.coupon_code = '';
}

async function renderBakongQr() {
    const qrValue = staticBakongQrPayload;

    if (bakongQrDataUrl.value || bakongQrSvg.value) {
        return;
    }

    try {
        bakongQrDataUrl.value = await QRCode.toDataURL(qrValue, {
            errorCorrectionLevel: 'M',
            margin: 1,
            width: 320,
            color: {
                dark: '#0f172a',
                light: '#ffffff',
            },
        });
        bakongQrSvg.value = '';
    } catch (error) {
        try {
            bakongQrSvg.value = await QRCode.toString(qrValue, {
                type: 'svg',
                errorCorrectionLevel: 'M',
                margin: 1,
                width: 320,
                color: {
                    dark: '#0f172a',
                    light: '#ffffff',
                },
            });
            bakongQrDataUrl.value = '';
        } catch (svgError) {
            console.error('Bakong QR render failed:', error, svgError);
            bakongQrDataUrl.value = '';
            bakongQrSvg.value = '';
        }
    }
}

function downloadPaymentReference() {
    if (!bakongQrDataUrl.value) return;

    const link = document.createElement('a');

    link.href = bakongQrDataUrl.value;
    link.download = `${checkoutResult.value?.order_number ?? 'bakong-qr'}.png`;
    document.body.appendChild(link);
    link.click();
    link.remove();
}

function formatDuration(totalSeconds) {
    const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
    const seconds = String(totalSeconds % 60).padStart(2, '0');

    return `${minutes}:${seconds}`;
}

onBeforeUnmount(() => {
    clearPaymentTimer();
    clearPaymentSuccessTimer();

    if (geocodeTimer) {
        window.clearTimeout(geocodeTimer);
    }
});
</script>

<template>
    <Head title="Checkout" />

    <UserLayout>
        <div v-if="cart.length === 0 && !checkoutResult" class="container mx-auto px-4 py-24 text-center">
            <h1 class="text-2xl font-bold">Your cart is empty</h1>
            <Link
                :href="route('shop')"
                class="mt-5 inline-block rounded-full bg-primary px-7 py-3 text-primary-foreground font-medium hover:bg-primary/90"
            >
                Go shopping
            </Link>
        </div>

        <div v-else class="container mx-auto px-4 py-8">
            <UserBreadcrumb
                :items="[
                    { label: 'Home', href: route('home') },
                    { label: 'Cart', href: route('cart') },
                    { label: 'Checkout' },
                ]"
            />

            <h1 class="mt-2 text-3xl md:text-4xl font-bold">Checkout</h1>

            <div class="mt-6 flex items-center gap-3 text-xs flex-wrap">
                <div v-for="(step, i) in ['Cart', 'Details', 'Payment']" :key="step" class="flex items-center gap-2">
                    <span
                        class="h-7 w-7 rounded-full grid place-items-center font-bold"
                        :class="i <= 1 ? 'bg-primary text-primary-foreground' : 'bg-secondary text-muted-foreground'"
                    >
                        {{ i + 1 }}
                    </span>
                    <span :class="i <= 1 ? 'font-semibold' : 'text-muted-foreground'">{{ step }}</span>
                    <span v-if="i < 2" class="w-8 h-px bg-border mx-2 hidden sm:block" />
                </div>
            </div>

            <div
                v-if="Object.keys(errors).length"
                class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
            >
                <p class="font-semibold">Could not place your order</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    <li v-for="(message, key) in errors" :key="key">{{ message }}</li>
                </ul>
            </div>

            <form class="mt-8 grid lg:grid-cols-[1fr_380px] gap-8" @submit.prevent="placeOrder">
                <div class="space-y-6">
                    <section class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                        <h3 class="font-bold mb-4">Contact & delivery</h3>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Full name</span>
                                <input
                                    v-model="form.name"
                                    required
                                    :readonly="!!authUser?.name"
                                    class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 read-only:bg-muted/30 read-only:text-muted-foreground"
                                    placeholder="John Sok"
                                />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Phone</span>
                                <input
                                    v-model="form.phone"
                                    required
                                    type="tel"
                                    :readonly="!!authUser?.phone"
                                    class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 read-only:bg-muted/30 read-only:text-muted-foreground"
                                    placeholder="+855 12 345 678"
                                />
                            </label>
                            <label class="block sm:col-span-2">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Email</span>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    :readonly="!!authUser?.email"
                                    class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 read-only:bg-muted/30 read-only:text-muted-foreground"
                                    placeholder="you@email.com"
                                />
                            </label>
                            <label class="block sm:col-span-2">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Delivery address</span>
                                <input
                                    v-model="form.address"
                                    required
                                    class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10"
                                    placeholder="Street, building, apartment"
                                    @focus="openAddressMap"
                                    @input="openAddressMap"
                                />
                                <button
                                    v-if="form.address.trim().length >= 3 && !hasDeliveryLocation"
                                    type="button"
                                    class="mt-2 text-xs font-semibold text-primary hover:underline"
                                    @click="confirmAddressOnMap"
                                >
                                    Find this address on map
                                </button>
                                <div
                                    v-if="showAddressMap"
                                    class="mt-3 overflow-hidden rounded-2xl border border-border/60 bg-secondary/20"
                                >
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border/60 bg-background px-4 py-3">
                                        <div class="flex items-center gap-2 text-sm font-semibold">
                                            <MapPin class="h-4 w-4 text-primary" />
                                            Delivery map
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-background px-3 py-1.5 text-xs font-semibold text-foreground transition hover:border-primary hover:text-primary disabled:opacity-50"
                                                :disabled="mapLoading"
                                                @click="useCurrentLocation"
                                            >
                                                <Navigation class="h-3.5 w-3.5" />
                                                GPS location
                                            </button>
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-1.5 rounded-full border border-primary/30 bg-primary/5 px-3 py-1.5 text-xs font-semibold text-primary transition hover:border-primary hover:bg-primary/10 disabled:opacity-50"
                                                :disabled="mapLoading"
                                                @click="useNetworkLocation"
                                            >
                                                <Wifi class="h-3.5 w-3.5" />
                                                Network location
                                            </button>
                                            <span
                                                v-if="deliveryDistanceKm != null"
                                                class="rounded-full bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary"
                                            >
                                                {{ deliveryDistanceKm }} km from store
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        v-if="geolocationBlocked && !mapLoading"
                                        class="border-b border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-950"
                                    >
                                        <p class="font-semibold">Enable GPS for this website</p>
                                        <ol class="mt-2 list-decimal space-y-1 pl-5 text-xs leading-relaxed">
                                            <li>
                                                Click the <strong>lock or tune icon</strong> left of the address bar →
                                                <strong>Location</strong> → <strong>Allow</strong>
                                            </li>
                                            <li><strong>Reload</strong> this page</li>
                                            <li>Click <strong>GPS location</strong> again and choose Allow</li>
                                        </ol>
                                        <p class="mt-2 text-xs leading-relaxed">
                                            Windows: Settings → Privacy &amp; security → Location → On, and allow your
                                            browser. Or use <strong>Network location</strong> / tap the map below.
                                        </p>
                                    </div>

                                    <p
                                        v-else-if="locationHint && !mapLoading"
                                        class="border-b border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                                    >
                                        {{ locationHint }}
                                    </p>

                                    <div v-if="mapLoading" class="flex items-center justify-center gap-2 px-4 py-10 text-sm text-muted-foreground">
                                        <Loader2 class="h-4 w-4 animate-spin" />
                                        Loading map…
                                    </div>

                                    <template v-else>
                                        <p v-if="mapError" class="border-b border-rose-200/80 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                            {{ mapError }}
                                        </p>

                                        <div
                                            v-if="hasDeliveryLocation"
                                            class="border-b border-border/60 bg-background px-4 py-3"
                                        >
                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                                                Delivery location
                                            </p>
                                            <p class="mt-1 text-sm font-medium text-foreground break-words">
                                                {{ deliveryLocationLabel }}
                                            </p>
                                        </div>

                                        <DeliveryLocationMap
                                            :key="`${storeLat}-${storeLng}`"
                                            :lat="deliveryLat"
                                            :lng="deliveryLng"
                                            :store-lat="storeLat"
                                            :store-lng="storeLng"
                                            :store-name="storeMapLabel"
                                            :delivery-label="deliveryLocationLabel"
                                            @pick="onMapLocationPick"
                                        />

                                        <p class="px-4 py-3 text-center text-xs text-muted-foreground">
                                            <template v-if="hasDeliveryLocation">
                                                Drag the pin or tap the map to adjust your delivery spot.
                                            </template>
                                            <template v-else>
                                                Tap the map, use GPS / Network location, or type your address above.
                                            </template>
                                        </p>

                                        <div
                                            v-if="hasDeliveryLocation"
                                            class="flex flex-wrap items-center justify-between gap-2 border-t border-border/60 px-4 pb-3 pt-2"
                                        >
                                            <p class="text-xs text-muted-foreground">
                                                Route from
                                                <span class="font-medium text-foreground">{{ storeMapLabel }}</span>
                                                ({{ storeLat.toFixed(5) }}, {{ storeLng.toFixed(5) }}) to your delivery
                                                address.
                                            </p>
                                            <a
                                                :href="googleMapsUrl"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-background px-3 py-1.5 text-xs font-semibold text-primary transition hover:border-primary"
                                            >
                                                <Navigation class="h-3.5 w-3.5" />
                                                Open in Maps
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </label>

                            <label class="block sm:col-span-2">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Qty kilo</span>
                                <input
                                    v-model="form.qty_kilo"
                                    type="number"
                                    min="1"
                                    step="0.001"
                                    class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10"
                                    placeholder="1.000"
                                    @focus="qtyKiloTouched = true"
                                    @input="qtyKiloTouched = true"
                                />
                            </label>

                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Floor</span>
                                <input v-model="form.floor" class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="Floor 2" />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                        <h3 class="font-bold mb-4">Payment method</h3>
                        <div class="grid sm:grid-cols-3 gap-3">
                            <label
                                v-for="m in methods"
                                :key="m.id"
                                class="relative cursor-pointer rounded-2xl border p-4 block transition-all"
                                :class="method === m.id ? 'border-primary bg-primary/5 shadow-soft' : 'border-border hover:border-primary/40'"
                            >
                                <input v-model="method" type="radio" name="paymentMethod" :value="m.id" class="sr-only" />
                                <component :is="m.icon" class="h-6 w-6 text-primary" />
                                <p class="mt-3 font-semibold text-sm">{{ m.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ m.desc }}</p>
                                <CheckCircle2 v-if="method === m.id" class="absolute top-3 right-3 h-5 w-5 text-primary" />
                            </label>
                        </div>
                        <!-- <p v-if="errors.payment_method" class="mt-3 text-sm font-medium text-rose-600">
                            {{ errors.payment_method }}
                        </p> -->
<!-- 
                        <div v-if="method === 'card'" class="mt-4 grid sm:grid-cols-2 gap-3">
                            <label class="block sm:col-span-2">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Card number</span>
                                <input class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="1234 5678 9012 3456" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Expiry</span>
                                <input class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="MM/YY" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">CVC</span>
                                <input class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="123" />
                            </label>
                        </div> -->
                    </section>

                    <section class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                        <h3 class="font-bold mb-4">Order notes (optional)</h3>
                        <textarea v-model="form.note"
                            rows="3"
                            placeholder="Delivery instructions, gift notes…"
                            class="w-full rounded-2xl border bg-background p-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 resize-none"
                        />
                    </section>

                    <section class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                        <h3 class="font-bold mb-4">Coupon</h3>
                        <label class="block">
                            <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Enter coupon code</span>
                            <input
                                v-model="form.coupon_code"
                                type="text"
                                class="w-full h-11 rounded-xl border bg-background px-4 text-sm uppercase tracking-wider outline-none focus:border-primary focus:ring-4 focus:ring-primary/10"
                                placeholder="PROMO2026"
                            />
                        </label>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="rounded-full bg-emerald-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-600 disabled:opacity-60"
                                :disabled="couponSaving || !form.coupon_code"
                                @click="applyCoupon"
                            >
                                {{ couponSaving ? 'Saving...' : 'Save Coupon' }}
                            </button>
                            <button
                                v-if="appliedCoupon"
                                type="button"
                                class="rounded-full border border-border px-4 py-2 text-xs font-bold text-muted-foreground transition hover:bg-secondary"
                                @click="clearCoupon"
                            >
                                Remove
                            </button>
                        </div>
                        <p v-if="couponError" class="mt-2 text-xs font-medium text-rose-600">
                            {{ couponError }}
                        </p>
                        <p v-else-if="appliedCoupon" class="mt-2 text-xs font-medium text-emerald-600">
                            Coupon applied. Discount: -${{ couponDiscount.toFixed(2) }}
                        </p>
                        <p v-else class="mt-2 text-xs text-muted-foreground">
                            Save the coupon to preview the reduced order total before placing the order.
                        </p>
                    </section>
                </div>

                <aside class="h-fit rounded-3xl bg-card border border-border/60 shadow-card p-6 lg:sticky lg:top-32">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-bold">Your order</h3>
                        <span class="rounded-full bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary">
                            Delivery ${{ deliveryFeePerKg.toFixed(2) }} / {{ addressQtyKg.toFixed(2) }} KM
                        </span>
                    </div>
                    <div class="mt-4 space-y-3 max-h-72 overflow-auto pr-1">
                        <div v-for="{ product, qty } in cart" :key="product.id" class="flex gap-3 items-center text-sm">
                            <div class="relative h-14 w-14 rounded-xl overflow-hidden bg-secondary shrink-0">
                                <img :src="product.image" alt="" class="h-full w-full object-cover" />
                                <span class="absolute -top-1 -right-1 h-5 w-5 grid place-items-center rounded-full bg-foreground text-background text-[10px] font-bold">
                                    {{ qty }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium line-clamp-1">{{ product.name }}</p>
                                <p class="text-xs text-muted-foreground">${{ product.price.toFixed(2) }}</p>
                            </div>
                            <p class="font-semibold">${{ (product.price * qty).toFixed(2) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>${{ cartSubtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Discount</span>
                            <span class="text-destructive">-${{ cartDiscount.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Delivery fee</span>
                            <span class="font-medium text-slate-700">{{ deliveryFormulaLabel }} = ${{ couponDeliveryFee.toFixed(2) }}</span>
                        </div>
                        <div v-if="appliedCoupon" class="flex justify-between">
                            <span class="text-muted-foreground">Coupon discount</span>
                            <span class="font-medium text-destructive">-${{ couponDiscount.toFixed(2) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">${{ placeOrderTotal.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div v-if="checkoutResult" class="mt-4 rounded-2xl border border-border/60 bg-background p-4 text-sm space-y-2">
                        <div v-if="checkoutCouponCode" class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Coupon code</span>
                            <span class="font-semibold text-foreground">{{ checkoutCouponCode }}</span>
                        </div>
                        <div v-if="checkoutCouponCode" class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Coupon type</span>
                            <span class="font-semibold text-foreground">{{ checkoutCouponType }}</span>
                        </div>
                        <div v-if="checkoutCouponCode" class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Coupon value</span>
                            <span class="font-semibold text-foreground">${{ checkoutCouponValue.toFixed(2) }}</span>
                        </div>
                        <div v-if="checkoutCouponCode" class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Coupon discount</span>
                            <span class="font-semibold text-foreground">-${{ checkoutCouponDiscount.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground">delivery_fee</span>
                            <span class="font-semibold text-foreground">${{ checkoutDeliveryFee.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Discount type</span>
                            <span class="font-semibold text-foreground">{{ checkoutDiscountType }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Discount value</span>
                            <span class="font-semibold text-foreground">${{ checkoutDiscountValue.toFixed(2) }}</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full mt-5 rounded-full bg-primary py-3 text-primary-foreground font-medium shadow-glow hover:bg-primary/90 disabled:opacity-60"
                        :disabled="placing"
                    >
                        {{ placing ? 'Placing order…' : `Place order · $${placeOrderTotal.toFixed(2)}` }}
                    </button>

                    <p class="mt-3 text-xs text-muted-foreground flex items-center gap-1.5">
                        <ShieldCheck class="h-3.5 w-3.5 text-primary" />
                        Encrypted, secure checkout
                    </p>
                </aside>
            </form>
        </div>

        <Transition name="modal-fade">
            <div v-if="showOrderSuccessModal && checkoutResult" class="fixed inset-0 z-[210] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-md" @click="closeOrderSuccessModal"></div>
                <div class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
                    <div class="p-6 text-center">
                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <CheckCircle2 class="h-8 w-8" />
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-slate-900">Order placed successfully!</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ orderSuccessDescription }}</p>
                        <p class="mt-3 font-mono text-sm font-semibold text-primary">
                            {{ checkoutResult.order_number }}
                        </p>
                        <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                            <Link
                                :href="route('user.profile')"
                                class="flex-1 rounded-full border border-border py-3 text-sm font-semibold text-foreground transition hover:bg-secondary"
                                @click="closeOrderSuccessModal"
                            >
                                View orders
                            </Link>
                            <Link
                                :href="route('shop')"
                                class="flex-1 rounded-full bg-primary py-3 text-center text-sm font-semibold text-primary-foreground shadow-glow transition hover:bg-primary/90"
                                @click="closeOrderSuccessModal"
                            >
                                Continue shopping
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="modal-fade">
            <div v-if="showKhqrModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-md" @click="closeKhqrModal"></div>

                <div class="relative w-full max-w-sm overflow-hidden rounded-[2rem] bg-white shadow-[0_30px_80px_rgba(0,0,0,0.35)]">
                    <button
                        type="button"
                        class="absolute right-4 top-4 z-10 h-10 w-10 rounded-full border border-slate-300/80 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50"
                        @click="closeKhqrModal"
                    >
                        <X class="mx-auto h-5 w-5" />
                    </button>

                    <div class="px-5 pt-5">
                        <div class="flex items-center gap-3">
                            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/25">
                                <QrCode class="h-6 w-6" />
                            </div>
                            <div>
                                <p class="text-lg font-black text-slate-900">Bakong KHQR</p>
                                <p class="text-sm text-slate-500">Scan to pay with Bakong KHQR</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 px-5 pb-5">
                        <div class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)]">
                            <div class="relative bg-emerald-500 px-4 py-3 text-center">
                                <p class="text-xl font-black tracking-[0.45em] text-white">KHQR</p>
                                <span class="absolute -bottom-2 right-6 h-4 w-4 rotate-45 bg-emerald-500"></span>
                            </div>

                            <div class="px-5 pb-5 pt-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">CAMBOMART</p>
                                <div class="mt-1 flex items-end gap-2">
                                    <span class="text-4xl font-black leading-none text-slate-900">${{ qrAmount }}</span>
                                    <span class="pb-1 text-base font-semibold text-slate-500">USD</span>
                                </div>

                                <div class="my-4 h-px bg-slate-200"></div>

                                <div class="mx-auto w-fit rounded-2xl bg-white p-3">
                                    <img
                                        v-if="bakongQrDataUrl"
                                        :src="bakongQrDataUrl"
                                        alt="Bakong QR"
                                        class="h-[320px] w-[320px] rounded-xl bg-white object-contain"
                                    />
                                    <div
                                        v-else-if="bakongQrSvg"
                                        class="h-[320px] w-[320px] overflow-hidden rounded-xl bg-white"
                                        v-html="bakongQrSvg"
                                    ></div>
                                    <div
                                        v-else
                                        class="grid h-[320px] w-[320px] place-items-center rounded-xl border border-dashed border-slate-200 text-sm text-slate-500"
                                    >
                                        Generating QR...
                                    </div>
                                </div>
                                <div class="mt-5 flex items-center justify-center text-center">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50"
                                        @click="downloadPaymentReference"
                                    >
                                        <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-200 bg-white shadow-sm">
                                            <Download class="h-3.5 w-3.5" />
                                        </span>
                                        Download
                                    </button>
                                </div>

                                <div class="mt-6 flex items-end justify-between gap-4">
                                    <div>
                                        <p class="flex items-center gap-1.5 text-xs text-slate-500">
                                            <Clock3 class="h-3.5 w-3.5" />
                                            Expires in
                                        </p>
                                        <p class="mt-1 font-mono text-lg font-black text-slate-900">{{ expiryLabel }}</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="rounded-full bg-emerald-500 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-70"
                                        :disabled="placing || paymentExpirySeconds === 0"
                                        @click="confirmOnlinePayment"
                                    >
                                        {{ placing ? 'Placing order…' : "I've paid · Confirm order" }}
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 text-center text-[11px] font-bold uppercase tracking-[0.35em] text-slate-500">
                                Powered by Cambomart Pay
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </UserLayout>
</template>
