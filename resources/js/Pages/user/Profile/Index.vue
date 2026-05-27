<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { User, Package, Heart, Settings, LogOut, Activity, MapPin, Edit2 } from 'lucide-vue-next';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

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

const recentActivities = [
    'Placed order CMB-23981 · $47.50',
    'Added 3 items to wishlist',
    'Updated delivery address',
    'Reviewed Organic Red Apples ★★★★★',
    'Joined the rewards program',
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

        <div class="container mx-auto px-4 py-8">
            <UserBreadcrumb :items="[{ label: 'Home', href: route('home') }, { label: 'Profile' }]" />

            <div class="mt-4 rounded-3xl bg-gradient-brand p-8 text-primary-foreground relative overflow-hidden">
                <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-background/10 blur-2xl" />
                <div class="relative flex items-center gap-5">
                    <div class="h-20 w-20 rounded-2xl bg-background/20 backdrop-blur grid place-items-center text-3xl font-bold">
                        {{ initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl md:text-3xl font-bold">{{ displayName }}</h1>
                        <p class="text-sm opacity-90 mt-0.5">{{ displayEmail }} · Member since Jan 2024</p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-full bg-background/20 backdrop-blur">⭐ Gold member</span>
                            <span class="px-2.5 py-1 rounded-full bg-background/20 backdrop-blur">28 orders</span>
                            <span class="px-2.5 py-1 rounded-full bg-background/20 backdrop-blur">$1,240 saved</span>
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
                        <div class="grid sm:grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-card border border-border/60 p-5 shadow-soft">
                                <p class="text-xs text-muted-foreground">Total orders</p>
                                <p class="text-2xl font-bold mt-1">28</p>
                            </div>
                            <div class="rounded-2xl bg-card border border-border/60 p-5 shadow-soft">
                                <p class="text-xs text-muted-foreground">In progress</p>
                                <p class="text-2xl font-bold mt-1">2</p>
                            </div>
                            <div class="rounded-2xl bg-card border border-border/60 p-5 shadow-soft">
                                <p class="text-xs text-muted-foreground">Reward points</p>
                                <p class="text-2xl font-bold mt-1">1,240</p>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                            <h3 class="font-bold mb-4">Recent orders</h3>
                            <div class="space-y-3">
                                <div
                                    v-for="order in mockOrders.slice(0, 3)"
                                    :key="order.id"
                                    class="flex items-center justify-between py-3 border-b last:border-0 text-sm"
                                >
                                    <div>
                                        <p class="font-semibold">{{ order.id }}</p>
                                        <p class="text-muted-foreground text-xs">{{ order.date }} · {{ order.items }} items</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary">{{ order.status }}</span>
                                        <p class="font-bold mt-1">${{ order.total.toFixed(2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
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
                        </div>
                    </template>

                    <template v-if="currentTab === 'orders'">
                        <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                            <h3 class="font-bold mb-4">Order history</h3>
                            <div class="space-y-3">
                                <div
                                    v-for="order in mockOrders"
                                    :key="order.id"
                                    class="flex items-center justify-between py-3 border-b last:border-0 text-sm"
                                >
                                    <div>
                                        <p class="font-semibold">{{ order.id }}</p>
                                        <p class="text-muted-foreground text-xs">{{ order.date }} · {{ order.items }} items</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary">{{ order.status }}</span>
                                        <p class="font-bold mt-1">${{ order.total.toFixed(2) }}</p>
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
                        <div class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                            <h3 class="font-bold mb-4">Account settings</h3>
                            <div class="grid sm:grid-cols-2 gap-3">
                                <label class="block">
                                    <span class="text-xs text-muted-foreground mb-1 block">Full name</span>
                                    <input :value="displayName" class="w-full h-11 rounded-xl border px-4 text-sm bg-background" />
                                </label>
                                <label class="block">
                                    <span class="text-xs text-muted-foreground mb-1 block">Email</span>
                                    <input :value="displayEmail" type="email" class="w-full h-11 rounded-xl border px-4 text-sm bg-background" />
                                </label>
                            </div>
                            <Link
                                :href="route('profile.edit')"
                                class="mt-5 inline-block px-6 h-11 leading-[2.75rem] rounded-full bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90"
                            >
                                Edit account (Laravel profile)
                            </Link>
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
</style>
