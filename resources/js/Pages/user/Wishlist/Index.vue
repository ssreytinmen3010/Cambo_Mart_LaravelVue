<script setup>
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
</script>

<template>
    <Head title="Wishlist" />
    <UserLayout>
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-3xl font-bold">Wishlist</h1>
            <p class="mt-2 text-muted-foreground">Save your favorite products here.</p>
        </div>
    </UserLayout>
</template>
