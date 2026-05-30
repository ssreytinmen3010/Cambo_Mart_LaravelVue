<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Truck, Wallet, CheckCircle2, ShieldCheck, Clock3, X, QrCode, Download } from 'lucide-vue-next';
import QRCode from 'qrcode';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';
import { useStore } from '@/composables/useStore';

const { cart, cartSubtotal, cartDiscount, cartTotal, clearCart, ensureCartLoaded } = useStore();
const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);
const errors = computed(() => page.props.errors ?? {});

onMounted(() => {
    ensureCartLoaded();

    if (authUser.value) {
        form.value.name = authUser.value.name ?? form.value.name;
        form.value.phone = authUser.value.phone ?? form.value.phone;
        form.value.email = authUser.value.email ?? form.value.email;
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
const paymentSuccessDelayMs = 60000;

const methods = [
    // { id: 'card', name: 'Credit / Debit card', desc: 'Visa, Mastercard, JCB', icon: CreditCard },
    { id: 'aba', name: 'Online Pay', desc: 'Scan with the ABA mobile app', icon: Wallet },
    { id: 'cod', name: 'Cash on delivery', desc: 'Pay when you receive your order', icon: Truck },
];

const total = computed(() => cartTotal.value);
const qrAmount = computed(() => total.value.toFixed(2));
const checkoutResult = computed(() => page.props.flash?.checkout ?? null);
const expiryLabel = computed(() => formatDuration(paymentExpirySeconds.value));

const form = ref({
    name: '',
    phone: '',
    email: '',
    address: '',
    floor: '',
    note: '',
});

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
            payment_method,
        },
        {
            onFinish: () => {
                placing.value = false;
            },
            onSuccess: () => {
                clearCart();
            },
        }
    );
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
                                <input v-model="form.address" required class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="Street, building, apartment" />
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
                </div>

                <aside class="h-fit rounded-3xl bg-card border border-border/60 shadow-card p-6 lg:sticky lg:top-32">
                    <h3 class="font-bold">Your order</h3>
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
                        <div class="border-t pt-3 flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">${{ total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full mt-5 rounded-full bg-primary py-3 text-primary-foreground font-medium shadow-glow hover:bg-primary/90 disabled:opacity-60"
                        :disabled="placing"
                    >
                        {{ placing ? 'Placing order…' : `Place order · $${total.toFixed(2)}` }}
                    </button>

                    <p class="mt-3 text-xs text-muted-foreground flex items-center gap-1.5">
                        <ShieldCheck class="h-3.5 w-3.5 text-primary" />
                        Encrypted, secure checkout
                    </p>
                </aside>
            </form>
        </div>

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
                                        Close
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
