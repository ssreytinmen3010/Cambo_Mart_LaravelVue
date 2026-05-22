<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import ProductCard from '@/Components/User/ProductCard.vue';

const categories = [
    {
        id: 1,
        name: 'Vegetables',
        icon: '🥦',
        count: 120,
        image: 'https://images.unsplash.com/photo-1542838132-92c53300491e',
    },
    {
        id: 2,
        name: 'Fruits',
        icon: '🍎',
        count: 90,
        image: 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b',
    },
    {
        id: 3,
        name: 'Dairy',
        icon: '🥛',
        count: 45,
        image: 'https://images.unsplash.com/photo-1628088062854-d1870b4553da',
    },
    {
        id: 4,
        name: 'Bakery',
        icon: '🍞',
        count: 60,
        image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff',
    },
    {
        id: 5,
        name: 'Spices',
        icon: '🌶️',
        count: 30,
        image: 'https://images.unsplash.com/photo-1604908812840-7bcd84e8aa3f',
    },
    {
        id: 6,
        name: 'Drinks',
        icon: '💧',
        count: 40,
        image: 'https://images.unsplash.com/photo-1526401485004-2aa7d4a3b70a',
    },
    {
        id: 7,
        name: 'Pantry',
        icon: '🫙',
        count: 55,
        image: 'https://images.unsplash.com/photo-1604909054203-3d5dd01cf15d',
    },
    {
        id: 8,
        name: 'Household',
        icon: '🧻',
        count: 25,
        image: 'https://images.unsplash.com/photo-1583947215259-38e31be8751f',
    },
];

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
        category: 2,
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
        category: 1,
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
        category: 3,
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
        category: 4,
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
        category: 5,
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
        category: 5,
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
        category: 6,
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
        category: 7,
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

const activeCategoryId = ref(categories[0]?.id ?? 1);
const activeBrandId = ref('');

const filteredProducts = computed(() =>
    products.filter((product) => {
        const matchesCategory = product.category === activeCategoryId.value;
        const matchesBrand = !activeBrandId.value || product.brand === activeBrandId.value;
        return matchesCategory && matchesBrand;
    }),
);

const activeCategory = computed(() => categories.find((category) => category.id === activeCategoryId.value));
</script>

<template>
    <Head>
        <title>Categories — CamboMart</title>
        <meta
            name="description"
            content="Browse all CamboMart product categories from fresh fruit and vegetables to bakery, dairy, and household."
        />
        <meta property="og:title" content="Categories — CamboMart" />
        <meta property="og:description" content="Browse all CamboMart product categories." />
    </Head>

    <UserLayout>
        <div class="container mx-auto px-4 py-8">
            <p class="text-xs text-muted-foreground">
                <Link href="/" class="hover:text-primary">Home</Link> / Categories
            </p>

            <h1 class="mt-2 text-3xl md:text-4xl font-bold">Shop by category</h1>
            <p class="mt-1 text-muted-foreground">
                Find exactly what you need across {{ categories.length }} curated collections.
            </p>

            <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-3">
                <button
                    v-for="c in categories"
                    :key="c.id"
                    type="button"
                    class="group block text-left rounded-3xl overflow-hidden border transition-all"
                    :class="
                        activeCategoryId === c.id
                            ? 'border-primary shadow-hover ring-2 ring-primary/20'
                            : 'border-border/60 hover:border-primary/40 hover:shadow-card'
                    "
                    @click="activeCategoryId = c.id"
                >
                    <div class="aspect-square relative bg-gradient-to-br from-primary-soft to-secondary">
                        <img
                            :src="c.image"
                            :alt="c.name"
                            class="absolute inset-0 h-full w-full object-cover opacity-70 group-hover:scale-110 transition-transform duration-500"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-background/95 via-background/30 to-transparent" />
                        <div class="absolute bottom-0 inset-x-0 p-3">
                            <div class="text-2xl mb-1">{{ c.icon }}</div>
                            <p class="text-sm font-semibold">{{ c.name }}</p>
                            <p class="text-[10px] text-muted-foreground">{{ c.count }} items</p>
                        </div>
                    </div>
                </button>
            </div>

            <div class="mt-10 grid lg:grid-cols-[240px_1fr] gap-8">
                <aside class="space-y-3">
                    <h3 class="font-semibold text-sm">Filter by brand</h3>

                    <button
                        type="button"
                        class="block w-full text-left px-4 py-2.5 rounded-xl text-sm transition-colors"
                        :class="activeBrandId === '' ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary'"
                        @click="activeBrandId = ''"
                    >
                        All brands
                    </button>

                    <button
                        v-for="b in brands"
                        :key="b.id"
                        type="button"
                        class="block w-full text-left px-4 py-2.5 rounded-xl text-sm transition-colors"
                        :class="activeBrandId === b.id ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary'"
                        @click="activeBrandId = b.id"
                    >
                        <span class="mr-2">{{ b.logo }}</span>{{ b.name }}
                    </button>
                </aside>

                <div>
                    <h2 class="text-xl font-bold mb-4">
                        {{ activeCategory?.name }} ·
                        <span class="text-muted-foreground font-normal">
                            {{ filteredProducts.length }} products
                        </span>
                    </h2>

                    <div v-if="filteredProducts.length === 0" class="rounded-3xl border border-dashed py-20 text-center">
                        <p class="text-muted-foreground">No products in this combination.</p>
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <ProductCard v-for="p in filteredProducts" :key="p.id" :product="p" />
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
