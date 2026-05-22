<script setup>
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
</script>

<template>
    <Head title="Shop" />

    <UserLayout>
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-3xl font-bold">Shop</h1>
            <p class="mt-2 text-muted-foreground">Browse our full catalog of fresh products.</p>
        </div>
    </UserLayout>
</template>
