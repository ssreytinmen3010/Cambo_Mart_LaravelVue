<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { User, Package, Heart, Settings, LogOut, Activity, MapPin, Edit2 } from 'lucide-vue-next';
import { jsPDF } from 'jspdf';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const historyOrders = computed(() => page.props.historyOrders ?? []);
const recentActivities = computed(() => page.props.recentActivities ?? []);
const profileImagePreview = ref(authUser.value?.image ?? null);
const profileImageInput = ref(null);
const profileForm = useForm({
    name: authUser.value?.name ?? '',
    email: authUser.value?.email ?? '',
    phone: authUser.value?.phone ?? '',
    image: authUser.value?.image ?? null,
});

const showCheckoutResult = ref(false);
const checkoutResult = computed(() => page.props.flash?.checkout ?? null);
const checkoutPaymentStatus = computed(() => checkoutResult.value?.payment_status ?? null);
const checkoutTitle = computed(() => {
    if (!checkoutPaymentStatus.value) return 'Success';
    return checkoutPaymentStatus.value === 'PAID' ? 'Success' : 'Awaiting payment';
});
const checkoutDescription = computed(() => {
    if (!checkoutResult.value) return '';
    const paymentText = checkoutPaymentStatus.value ?? 'UNKNOWN';
    if (paymentText === 'PAID') return `Order ${checkoutResult.value.order_number} placed. Payment: PAID.`;
    return `Order ${checkoutResult.value.order_number} placed. Payment: ${paymentText}.`;
});

onMounted(() => {
    if (checkoutResult.value) showCheckoutResult.value = true;
});

const tabs = [
    { id: 'overview', label: 'Overview', icon: User },
    { id: 'orders', label: 'Orders', icon: Package },
    { id: 'wishlist', label: 'Wishlist', icon: Heart },
    { id: 'activity', label: 'Activity', icon: Activity },
    { id: 'settings', label: 'Settings', icon: Settings },
];

const mockOrders = [
    { id: 'CMB-23981', date: 'May 18, 2026', status: 'Delivered', total: 47.5, items: 4 },
    { id: 'CMB-23845', date: 'May 11, 2026', status: 'Shipped', total: 22.9, items: 2 },
    { id: 'CMB-23712', date: 'May 02, 2026', status: 'Delivered', total: 89.2, items: 7 },
    { id: 'CMB-23598', date: 'Apr 26, 2026', status: 'Delivered', total: 14.3, items: 1 },
];

const currentTab = ref('overview');

const displayName = computed(() => authUser.value?.name ?? 'Guest User');
const displayEmail = computed(() => authUser.value?.email ?? 'Sign in to view your account');
const initials = computed(() => {
    const name = displayName.value;
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});
const profileImage = computed(() => profileImagePreview.value ?? authUser.value?.image ?? null);
const totalOrders = computed(() => historyOrders.value.length);
const latestOrder = computed(() => visibleOrders.value[0] ?? null);

const selectProfileImage = () => {
    profileImageInput.value?.click();
};

const updateProfileImagePreview = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        profileImagePreview.value = e.target?.result ?? null;
    };
    reader.readAsDataURL(file);

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await window.axios.post(route('images.upload'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        profileForm.image = response.data.url;
    } catch (error) {
        console.error('Profile image upload failed', error);
        profileImagePreview.value = authUser.value?.image ?? null;
    }
};

const saveProfile = () => {
    profileForm.put(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['auth'] }),
    });
};

const inProgressOrders = computed(
    () => historyOrders.value.filter((order) => String(order.status).toUpperCase() === 'PENDING').length,
);

const visibleOrders = computed(() => historyOrders.value.length ? historyOrders.value : mockOrders);

const orderStatusClasses = (status) => {
    const value = String(status).toUpperCase();

    if (value === 'COMPLETED') return 'bg-emerald-50 text-emerald-700';
    if (value === 'PENDING') return 'bg-amber-50 text-amber-700';
    if (value === 'CANCELLED') return 'bg-red-50 text-red-700';

    return 'bg-slate-100 text-slate-600';
};

const selectedInvoiceOrder = ref(null);
const showInvoiceModal = ref(false);

const openInvoice = (order) => {
    selectedInvoiceOrder.value = order;
    showInvoiceModal.value = true;
};

const closeInvoiceModal = () => {
    showInvoiceModal.value = false;
};

const downloadReceiptPdf = () => {
    const order = selectedInvoiceOrder.value;
    if (!order) return;
    const doc = new jsPDF({ unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();
    const left = 16;
    const right = pageWidth - 16;
    const tableWidth = right - left;
    const status = String(order.status ?? 'UNKNOWN').toUpperCase();
    const statusColor =
        status === 'COMPLETED'
            ? [22, 163, 74]
            : status === 'PENDING'
                ? [217, 119, 6]
                : status === 'CANCELLED'
                    ? [220, 38, 38]
                    : [100, 116, 139];

    const drawLine = (y) => {
        doc.setDrawColor(226, 232, 240);
        doc.line(left, y, right, y);
    };
    const money = (value) => `$${Number(value ?? 0).toFixed(2)}`;
    const safeText = (value, width) => doc.splitTextToSize(String(value ?? ''), width);

    doc.setFillColor(255, 255, 255);
    doc.rect(0, 0, pageWidth, 297, 'F');

    doc.setTextColor(15, 23, 42);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(30);
    doc.text('INVOICE', left, 58);

    doc.setFontSize(13);
    doc.text('CamboMart', right, 18, { align: 'right' });
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(100, 116, 139);
    doc.text('123 Anywhere St., Phnom Penh', right, 24, { align: 'right' });
    doc.text('Tel: +855 123 456 789', right, 29, { align: 'right' });
    doc.text('support@cambomart.com', right, 34, { align: 'right' });

    doc.setFillColor(245, 247, 250);
    doc.roundedRect(right - 42, 40, 26, 8, 3, 3, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(8);
    doc.setTextColor(...statusColor);
    doc.text(status, right - 29, 45.5, { align: 'center' });

    doc.setTextColor(15, 23, 42);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(8.5);
    doc.text('Invoice No:', left, 71);
    doc.setFont('helvetica', 'normal');
    doc.text(order.id, 38, 71);

    doc.setFont('helvetica', 'bold');
    doc.text('Date:', 125, 71);
    doc.setFont('helvetica', 'normal');
    doc.text(order.date, 142, 71);

    doc.setFont('helvetica', 'bold');
    doc.text('Bill to:', left, 81);
    doc.setFont('helvetica', 'normal');
    doc.text(displayName.value, 38, 81);
    doc.text(displayEmail.value, 38, 86);
    if (order.address?.name) doc.text(order.address.name, 38, 91);
    if (order.address?.address) safeText(order.address.address, 56).forEach((line, index) => doc.text(line, 38, 96 + index * 5));

    drawLine(104);

    const col = { item: left, desc: 38, qty: 112, price: 137, amount: 165 };
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(8.5);
    doc.text('Item', col.item, 112);
    doc.text('Description', col.desc, 112);
    doc.text('Qty', col.qty, 112, { align: 'right' });
    doc.text('Unit Price', col.price, 112, { align: 'right' });
    doc.text('Amount', col.amount, 112, { align: 'right' });
    drawLine(116);

    let y = 125;
    const items = order.items_list ?? [];
    if (items.length) {
        items.forEach((item, index) => {
            const nameLines = safeText(item.name ?? 'Product', 70);
            const rowHeight = Math.max(7, nameLines.length * 4);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.text(String(index + 1), col.item, y);
            doc.text(nameLines, col.desc, y);
            doc.text(String(item.qty ?? 0), col.qty, y, { align: 'right' });
            doc.text(money(item.unit_price), col.price, y, { align: 'right' });
            doc.text(money(item.line_total), col.amount, y, { align: 'right' });
            y += rowHeight + 2;
        });
    } else {
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.setTextColor(100, 116, 139);
        doc.text('No item details available.', left, y);
        y += 10;
    }

    drawLine(y);

    const totalsY = y + 10;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(15, 23, 42);
    doc.text('Subtotal', 118, totalsY);
    // doc.text('Tax', 118, totalsY + 7);
    doc.text('Total', 118, totalsY + 14);
    doc.text(money(order.subtotal), right, totalsY, { align: 'right' });
    // doc.text('10%', right, totalsY + 7, { align: 'right' });
    doc.text(money(order.total), right, totalsY + 14, { align: 'right' });

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7.5);
    doc.setTextColor(100, 116, 139);
    doc.text('Payment Terms: Payment due upon receipt.', left, totalsY + 4);
    doc.text('Thank you for shopping with CamboMart.', pageWidth / 2, 286, { align: 'center' });

    doc.save(`${order.id}-invoice.pdf`);
};
</script>

<template>
    <Head title="My Profile" />

    <UserLayout>
        <!-- Checkout Result Modal -->
        <Transition name="modal-fade">
            <div v-if="showCheckoutResult" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showCheckoutResult = false"></div>
                <div class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-pop">
                    <div class="p-6">
                        <div
                            class="h-12 w-12 rounded-2xl grid place-items-center font-black"
                            :class="checkoutPaymentStatus === 'PAID' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                        >
                            {{ checkoutPaymentStatus === 'PAID' ? '✓' : '⏳' }}
                        </div>

                        <h3 class="mt-4 text-xl font-black text-slate-800">{{ checkoutTitle }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ checkoutDescription }}</p>

                        <button
                            type="button"
                            class="w-full mt-5 rounded-full bg-primary py-3 text-primary-foreground font-medium shadow-glow hover:bg-primary/90"
                            @click="showCheckoutResult = false"
                        >
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="modal-fade">
            <div v-if="showInvoiceModal" class="invoice-print-area fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div class="no-print absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="closeInvoiceModal"></div>
                <div class="relative w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                    <button
                        type="button"
                        class="no-print absolute right-4 top-4 z-10 h-10 w-10 rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50"
                        @click="closeInvoiceModal"
                    >
                        ×
                    </button>

                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Invoice</p>
                        <div class="mt-2 flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-2xl font-black text-slate-900">{{ selectedInvoiceOrder?.id }}</h3>
                                <p class="mt-1 text-sm text-slate-500">Generated on {{ selectedInvoiceOrder?.date }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="orderStatusClasses(selectedInvoiceOrder?.status)">
                                {{ selectedInvoiceOrder?.status }}
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-6 px-6 py-6 md:grid-cols-[1.2fr_0.8fr]">
                        <div class="space-y-5">
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Customer</p>
                                <div class="mt-3 text-sm text-slate-700">
                                    <p class="font-semibold">{{ displayName }}</p>
                                    <p>{{ displayEmail }}</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Delivery address</p>
                                <div class="mt-3 text-sm text-slate-700">
                                    <p class="font-semibold">{{ selectedInvoiceOrder?.address?.name ?? 'N/A' }}</p>
                                    <p>{{ selectedInvoiceOrder?.address?.phone ?? '' }}</p>
                                    <p class="mt-1">{{ selectedInvoiceOrder?.address?.address ?? 'No address provided' }}</p>
                                    <p v-if="selectedInvoiceOrder?.address?.floor" class="mt-1">Floor: {{ selectedInvoiceOrder.address.floor }}</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Items</p>
                                <div class="mt-3 space-y-3">
                                    <div
                                        v-for="item in selectedInvoiceOrder?.items_list ?? []"
                                        :key="item.name"
                                        class="flex items-center justify-between gap-4 text-sm"
                                    >
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ item.name }}</p>
                                            <p class="text-xs text-slate-500">Qty {{ item.qty }} × ${{ Number(item.unit_price).toFixed(2) }}</p>
                                        </div>
                                        <p class="font-semibold text-slate-900">${{ Number(item.line_total).toFixed(2) }}</p>
                                    </div>
                                    <p v-if="!selectedInvoiceOrder?.items_list?.length" class="text-sm text-slate-500">
                                        No item details available.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Invoice Summary</p>
                                <div class="mt-3 space-y-2 text-sm text-slate-700">
                                    <div class="flex items-center justify-between">
                                        <span>Subtotal</span>
                                        <span>${{ Number(selectedInvoiceOrder?.subtotal ?? 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Discount</span>
                                        <span>-${{ Number(selectedInvoiceOrder?.discount ?? 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-base font-bold text-slate-900">
                                        <span>Total</span>
                                        <span>${{ Number(selectedInvoiceOrder?.total ?? 0).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 p-4 text-sm text-slate-700">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Payment</p>
                                <p class="mt-3 font-semibold">{{ selectedInvoiceOrder?.payment_method ?? 'N/A' }}</p>
                                <p class="mt-1 text-slate-500">Items: {{ selectedInvoiceOrder?.items ?? 0 }}</p>
                            </div>

                            <button
                                type="button"
                                class="no-print w-full rounded-full bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                                @click="downloadReceiptPdf"
                            >
                                Download receipt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="container mx-auto px-4 py-8">
            <UserBreadcrumb :items="[{ label: 'Home', href: route('home') }, { label: 'Profile' }]" />
<!-- Main Container: Uses soft borders and subtle shadows instead of heavy colors -->
<div class="group relative mt-4 overflow-hidden rounded-[2rem] border border-neutral-200 bg-white/40 p-6 md:p-10 backdrop-blur-md transition-all hover:shadow-xl hover:shadow-neutral-200/50">
    
    <div class="relative flex flex-col items-center gap-6 sm:flex-row sm:gap-8">
        <!-- Profile Image: Neutral Squircle -->
        <div class="relative shrink-0">
            <div class="h-24 w-24 rounded-3xl bg-neutral-100 p-1 shadow-inner">
                <div class="grid h-full w-full place-items-center overflow-hidden rounded-[1.4rem] bg-neutral-200">
                    <img v-if="profileImage" :src="profileImage" alt="Profile" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                    <span v-else class="text-2xl font-bold text-neutral-500">{{ initials }}</span>
                </div>
            </div>
            <!-- Status Dot -->
            <span class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full border-4 border-white bg-emerald-500 shadow-sm" />
        </div>

        <!-- Text Content: High Contrast Neutral -->
        <div class="flex-1 text-center sm:text-left">
            <div class="flex flex-col gap-1">
                <h1 class="text-3xl font-bold tracking-tight text-neutral-900 md:text-4xl">
                    {{ displayName }}
                </h1>
                <p class="flex items-center justify-center gap-2 text-sm font-medium text-neutral-500 sm:justify-start">
                    <span>{{ displayEmail }}</span>
                    <span class="h-1 w-1 rounded-full bg-neutral-300"></span>
                    <span>Member since Jan 2024</span>
                </p>
            </div>

            <!-- Stats/Badges -->
            <div class="mt-5 flex flex-wrap justify-center gap-3 sm:justify-start">
                <div class="flex items-center gap-2 rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-2 transition-colors hover:bg-white">
                    <span class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Total Orders</span>
                    <span class="text-sm font-bold text-neutral-800">{{ totalOrders }}</span>
                </div>
                
                <!-- <button class="rounded-xl border border-neutral-200 bg-white px-4 py-2 text-xs font-semibold text-neutral-600 transition-all hover:bg-neutral-50 active:scale-95">
                    Settings
                </button> -->
            </div>
        </div>
    </div>
</div>

            <div class="mt-6 grid lg:grid-cols-[240px_1fr] gap-6">
                <aside class="space-y-1 lg:sticky lg:top-32 h-fit">
                    <button
                        v-for="t in tabs"
                        :key="t.id"
                        type="button"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-colors"
                        :class="currentTab === t.id ? 'bg-primary text-primary-foreground shadow-glow' : 'hover:bg-secondary'"
                        @click="currentTab = t.id"
                    >
                        <component :is="t.icon" class="h-4 w-4" />
                        {{ t.label }}
                    </button>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-destructive hover:bg-destructive/10 mt-4"
                    >
                        <LogOut class="h-4 w-4" /> Sign out
                    </Link>
                </aside>

                <div class="space-y-5">
                    <template v-if="currentTab === 'overview'">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-border/60 bg-card p-5 shadow-soft">
                                <p class="text-xs text-muted-foreground">History orders</p>
                                <p class="mt-1 text-2xl font-bold">{{ historyOrders.length }}</p>
                            </div>
                            <div class="rounded-2xl border border-border/60 bg-card p-5 shadow-soft">
                                <p class="text-xs text-muted-foreground">In progress</p>
                                <p class="mt-1 text-2xl font-bold">{{ inProgressOrders }}</p>
                            </div>
                           
                        </div>

                        <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                            <h3 class="font-bold mb-4">Recent orders</h3>
                            <div class="space-y-3">
                                <div
                                    v-for="order in visibleOrders.slice(0, 3)"
                                    :key="order.id"
                                    class="flex items-center justify-between py-3 border-b last:border-0 text-sm"
                                >
                                    <div>
                                        <p class="font-semibold">{{ order.id }}</p>
                                        <p class="text-muted-foreground text-xs">{{ order.date }} · {{ order.items }} items</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs px-2 py-0.5 rounded-full" :class="orderStatusClasses(order.status)">
                                            {{ order.status }}
                                        </span>
                                        <p class="font-bold mt-1">${{ order.total.toFixed(2) }}</p>
                                        <button
                                            type="button"
                                            class="mt-2 inline-flex items-center gap-1 rounded-full border border-border/60 px-3 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-primary hover:text-primary"
                                            @click="openInvoice(order)"
                                        >
                                            Receipt
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-bold">Delivery address</h3>
                                <button type="button" class="text-xs text-primary font-medium flex items-center gap-1">
                                    <Edit2 class="h-3 w-3" /> Edit
                                </button>
                            </div>
                            <div class="flex items-start gap-3 text-sm">
                                <MapPin class="h-5 w-5 text-primary mt-0.5 shrink-0" />
                                <div>
                                    <p class="font-semibold">Home</p>
                                    <p class="text-muted-foreground mt-0.5">#42, Street 271, Sangkat Toul Tum Poung, Phnom Penh, Cambodia</p>
                                </div>
                            </div>
                        </div> -->
                    </template>

                    <template v-if="currentTab === 'orders'">
                        <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                            <h3 class="font-bold mb-4">Order history</h3>
                            <div class="space-y-3">
                                <div
                                    v-for="order in visibleOrders"
                                    :key="order.id"
                                    class="flex items-center justify-between py-3 border-b last:border-0 text-sm"
                                >
                                    <div>
                                        <p class="font-semibold">{{ order.id }}</p>
                                        <p class="text-muted-foreground text-xs">{{ order.date }} · {{ order.items }} items</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs px-2 py-0.5 rounded-full" :class="orderStatusClasses(order.status)">
                                            {{ order.status }}
                                        </span>
                                        <p class="font-bold mt-1">${{ order.total.toFixed(2) }}</p>
                                        <button
                                            type="button"
                                            class="mt-2 inline-flex items-center gap-1 rounded-full border border-border/60 px-3 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-primary hover:text-primary"
                                            @click="openInvoice(order)"
                                        >
                                            Download receipt
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-if="currentTab === 'wishlist'">
                        <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                            <h3 class="font-bold mb-2">My wishlist</h3>
                            <Link :href="route('wishlist')" class="text-primary text-sm hover:underline">Open full wishlist →</Link>
                        </div>
                    </template>

                    <template v-if="currentTab === 'activity'">
                        <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                            <h3 class="font-bold mb-4">Activity</h3>
                            <ul class="space-y-3 text-sm">
                                <li
                                    v-for="(activity, index) in recentActivities"
                                    :key="index"
                                    class="flex items-center gap-3 py-2 border-b last:border-0"
                                >
                                    <span class="h-2 w-2 rounded-full bg-primary shrink-0" />
                                    <span class="flex-1">{{ activity }}</span>
                                    <span class="text-xs text-muted-foreground">{{ index + 1 }}d ago</span>
                                </li>
                            </ul>
                        </div>
                    </template>

                    <template v-if="currentTab === 'settings'">
                        <div class="space-y-5">
                            <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                                <h3 class="font-bold mb-4">Account settings</h3>
                                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="grid h-24 w-24 place-items-center overflow-hidden rounded-3xl border border-border/60 bg-slate-50 text-2xl font-black text-slate-500">
                                            <img
                                                v-if="profileImagePreview"
                                                :src="profileImagePreview"
                                                alt="Profile"
                                                class="h-full w-full object-cover"
                                            />
                                            <span v-else>{{ initials }}</span>
                                        </div>
                                        <input
                                            ref="profileImageInput"
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="updateProfileImagePreview"
                                        />
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-full border border-border/60 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-primary hover:text-primary"
                                            @click="selectProfileImage"
                                        >
                                            Upload image
                                        </button>
                                    </div>

                                    <div class="grid flex-1 gap-3 sm:grid-cols-2">
                                        <label class="block">
                                            <span class="mb-1 block text-xs text-muted-foreground">Full name</span>
                                            <input
                                                v-model="profileForm.name"
                                                type="text"
                                                class="w-full h-11 rounded-xl border px-4 text-sm bg-background"
                                            />
                                        </label>
                                        <label class="block">
                                            <span class="mb-1 block text-xs text-muted-foreground">Email</span>
                                            <input
                                                v-model="profileForm.email"
                                                type="email"
                                                class="w-full h-11 rounded-xl border px-4 text-sm bg-background"
                                            />
                                        </label>
                                        <label class="block sm:col-span-2">
                                            <span class="mb-1 block text-xs text-muted-foreground">Phone number</span>
                                            <input
                                                v-model="profileForm.phone"
                                                type="tel"
                                                class="w-full h-11 rounded-xl border px-4 text-sm bg-background"
                                                placeholder="+855 12 345 678"
                                            />
                                        </label>

                                        <div class="sm:col-span-2 flex items-center justify-between gap-3">
                                            <p class="text-xs text-muted-foreground">
                                                Update your name, email, phone number, and profile image.
                                            </p>
                                            <button
                                                type="button"
                                                class="rounded-full bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/25 transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70"
                                                :disabled="profileForm.processing"
                                                @click="saveProfile"
                                            >
                                                Save profile
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                                <UpdatePasswordForm />
                            </div>

                            <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                                <DeleteUserForm />
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<style>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.animate-pop {
    animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes pop {
    0% {
        transform: scale(0.96);
        opacity: 0.5;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@media print {
    body * {
        visibility: hidden !important;
    }

    .invoice-print-area,
    .invoice-print-area * {
        visibility: visible !important;
    }

    .invoice-print-area {
        position: fixed;
        inset: 0;
        background: white !important;
    }

    .no-print {
        display: none !important;
    }
}
</style>
