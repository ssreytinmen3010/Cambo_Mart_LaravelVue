<script setup>
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
</script>

<template>
    <Head title="My Account" />

    <UserLayout>
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-3xl font-bold">My Dashboard</h1>
            <p class="mt-2 text-muted-foreground">Welcome back! Your account area is ready.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a
                    :href="route('shop')"
                    class="rounded-2xl border border-border/60 bg-card p-6 shadow-soft hover:border-primary/30 transition-colors"
                >
                    <p class="font-semibold">Continue shopping</p>
                    <p class="mt-1 text-sm text-muted-foreground">Browse fresh products</p>
                </a>

                <a
                    :href="route('cart')"
                    class="rounded-2xl border border-border/60 bg-card p-6 shadow-soft hover:border-primary/30 transition-colors"
                >
                    <p class="font-semibold">My cart</p>
                    <p class="mt-1 text-sm text-muted-foreground">View items in your basket</p>
                </a>

                <a
                    :href="route('user.profile')"
                    class="rounded-2xl border border-border/60 bg-card p-6 shadow-soft hover:border-primary/30 transition-colors"
                >
                    <p class="font-semibold">My profile</p>
                    <p class="mt-1 text-sm text-muted-foreground">Orders, wishlist, and account settings</p>
                </a>

                <a
                    :href="route('checkout')"
                    class="rounded-2xl border border-border/60 bg-card p-6 shadow-soft hover:border-primary/30 transition-colors"
                >
                    <p class="font-semibold">Checkout</p>
                    <p class="mt-1 text-sm text-muted-foreground">Complete your order</p>
                </a>
            </div>
        </div>
    </UserLayout>
</template>
