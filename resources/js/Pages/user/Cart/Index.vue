<script setup>
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
</script>

<template>
    <Head title="Cart" />
    <UserLayout>
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-3xl font-bold">Shopping Cart</h1>
            <p class="mt-2 text-muted-foreground">Your cart is empty. Start shopping to add items.</p>
        </div>
    </UserLayout>
</template>
