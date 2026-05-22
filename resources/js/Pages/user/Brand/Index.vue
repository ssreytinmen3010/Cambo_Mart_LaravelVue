<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import ProductCard from '@/Components/User/ProductCard.vue';

const brands = [
    { id: 'fresh-farms', name: 'Fresh Farms', logo: '🥬' },
    { id: 'mekong-fruit', name: 'Mekong Fruit', logo: '🥭' },
    { id: 'phnom-bakery', name: 'Phnom Bakery', logo: '🍞' },
    { id: 'dairy-house', name: 'Dairy House', logo: '🥛' },
    { id: 'angkor-spice', name: 'Angkor Spice', logo: '🌶️' },
    { id: 'kiri-water', name: 'Kiri Water', logo: '💧' },
    { id: 'kampot-pepper', name: 'Kampot Pepper', logo: '🧂' },
    { id: 'local-honey', name: 'Local Honey', logo: '🍯' },
];

const products = [
    {
        id: 101,
        brand: 'mekong-fruit',
        name: 'Organic Cambodian Mango',
        code: 'FRU-001',
        price: 4.99,
        oldPrice: 6.99,
        image: 'https://images.unsplash.com/photo-1553279768-865021837e9b',
        rating: 4.8,
        reviews: 124,
        badge: 'Sale',
        inStock: true,
    },
    {
        id: 102,
        brand: 'fresh-farms',
        name: 'Fresh Morning Glory',
        code: 'VEG-012',
        price: 1.49,
        image: 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        rating: 4.5,
        reviews: 89,
        badge: 'New',
        inStock: true,
    },
    {
        id: 103,
        brand: 'dairy-house',
        name: 'Farm Eggs (12 pack)',
        code: 'DAI-008',
        price: 3.25,
        image: 'https://images.unsplash.com/photo-1582722872405-2c03e42f8d83',
        rating: 4.9,
        reviews: 210,
        badge: 'Hot',
        inStock: true,
    },
    {
        id: 104,
        brand: 'phnom-bakery',
        name: 'Artisan Sourdough Loaf',
        code: 'BAK-003',
        price: 5.5,
        image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff',
        rating: 4.6,
        reviews: 67,
        inStock: false,
    },
    {
        id: 105,
        brand: 'angkor-spice',
        name: 'Lemongrass Spice Mix',
        code: 'SPI-021',
        price: 2.75,
        image: 'https://images.unsplash.com/photo-1604908812840-7bcd84e8aa3f',
        rating: 4.4,
        reviews: 52,
        badge: 'New',
        inStock: true,
    },
    {
        id: 106,
        brand: 'kampot-pepper',
        name: 'Kampot Black Pepper (100g)',
        code: 'SPI-010',
        price: 6.2,
        oldPrice: 7.5,
        image: 'https://images.unsplash.com/photo-1624388992148-24d069740cf5',
        rating: 4.9,
        reviews: 178,
        badge: 'Sale',
        inStock: true,
    },
    {
        id: 107,
        brand: 'kiri-water',
        name: 'Natural Mineral Water (1.5L)',
        code: 'WAT-001',
        price: 0.85,
        image: 'https://images.unsplash.com/photo-1526401485004-2aa7d4a3b70a',
        rating: 4.2,
        reviews: 31,
        inStock: true,
    },
    {
        id: 108,
        brand: 'local-honey',
        name: 'Raw Wildflower Honey (250g)',
        code: 'HON-004',
        price: 8.9,
        image: 'https://images.unsplash.com/photo-1514996937319-344454492b37',
        rating: 4.7,
        reviews: 95,
        badge: 'Hot',
        inStock: true,
    },
];

const activeBrandId = ref(brands[0]?.id ?? '');

const filteredProducts = computed(() => products.filter((product) => product.brand === activeBrandId.value));

const activeBrand = computed(() => brands.find((brand) => brand.id === activeBrandId.value));
</script>

<template>
    <Head>
        <title>Brands — CamboMart</title>
        <meta name="description" content="Explore the local Cambodian brands sold on CamboMart." />
        <meta property="og:title" content="Brands — CamboMart" />
        <meta property="og:description" content="Local Cambodian brands on CamboMart." />
    </Head>

    <UserLayout>
        <div class="container mx-auto px-4 py-8">
            <p class="text-xs text-muted-foreground">
                <Link href="/" class="hover:text-primary">Home</Link> / Brands
            </p>

            <h1 class="mt-2 text-3xl md:text-4xl font-bold">Trusted local brands</h1>
            <p class="mt-1 text-muted-foreground">
                Discover the families and growers behind every CamboMart product.
            </p>

            <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <button
                    v-for="brand in brands"
                    :key="brand.id"
                    type="button"
                    class="aspect-square rounded-2xl bg-card border flex flex-col items-center justify-center gap-2 transition-all"
                    :class="
                        activeBrandId === brand.id
                            ? 'border-primary shadow-hover ring-2 ring-primary/20'
                            : 'border-border/60 hover:border-primary/40 hover:shadow-card'
                    "
                    @click="activeBrandId = brand.id"
                >
                    <div class="text-3xl">{{ brand.logo }}</div>
                    <p class="text-xs font-semibold text-center px-2">{{ brand.name }}</p>
                </button>
            </div>

            <h2 class="mt-12 text-2xl font-bold">{{ activeBrand?.name }}</h2>
            <p class="text-sm text-muted-foreground mt-1">
                {{ filteredProducts.length }} products from this brand
            </p>

            <div class="mt-6 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                <ProductCard v-for="product in filteredProducts" :key="product.id" :product="product" />
            </div>
        </div>
    </UserLayout>
</template>
