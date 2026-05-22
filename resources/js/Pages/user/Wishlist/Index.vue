<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';
import ProductCard from '@/Components/User/ProductCard.vue';
import { useStore } from '@/composables/useStore';
import { products } from '@/lib/mock-data';

const { wishlist } = useStore();

const wishlistItems = computed(() => products.filter((p) => wishlist.includes(p.id)));
</script>

<template>
    <Head title="Wishlist" />

    <UserLayout>
        <div class="container mx-auto px-4 py-8">
            <UserBreadcrumb :items="[{ label: 'Home', href: route('home') }, { label: 'Wishlist' }]" />

            <h1 class="mt-2 text-3xl md:text-4xl font-bold">My wishlist</h1>
            <p class="mt-1 text-muted-foreground">
                {{ wishlistItems.length }} saved {{ wishlistItems.length === 1 ? 'item' : 'items' }}
            </p>

            <div v-if="wishlistItems.length === 0" class="mt-12 rounded-3xl border border-dashed py-20 text-center">
                <div class="h-20 w-20 mx-auto rounded-full bg-primary/10 text-primary grid place-items-center">
                    <Heart class="h-8 w-8" />
                </div>
                <p class="mt-5 text-lg font-semibold">No favorites yet</p>
                <p class="mt-1 text-sm text-muted-foreground">Tap the heart on any product to save it here.</p>
                <Link
                    :href="route('shop')"
                    class="mt-5 inline-block rounded-full bg-primary px-7 py-3 text-primary-foreground font-medium hover:bg-primary/90"
                >
                    Discover products
                </Link>
            </div>

            <div v-else class="mt-8 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <ProductCard v-for="product in wishlistItems" :key="product.id" :product="product" />
            </div>
        </div>
    </UserLayout>
</template>
