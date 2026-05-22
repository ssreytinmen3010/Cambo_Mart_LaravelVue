<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import ProductCard from '@/Components/User/ProductCard.vue';

const currentIndex = ref(0);

const promotions = ref([
    {
        title: 'Fresh Organic Vegetables',
        subtitle: 'Premium Cambodian farm products delivered fresh.',
        accent: 'Natural Fresh',
        image: 'https://images.unsplash.com/photo-1542838132-92c53300491e',
    },
    {
        title: 'Healthy Fruits Collection',
        subtitle: 'Sweet and fresh seasonal fruits.',
        accent: 'Best Seller',
        image: 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b',
    },
]);

const currentPromotion = computed(() => promotions.value[currentIndex.value]);

const categories = ref([
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
]);

const features = ref([
    { icon: '🚚', title: 'Free Delivery', desc: 'On orders over $25' },
    { icon: '🌿', title: '100% Natural', desc: 'Locally sourced quality' },
    { icon: '🛡️', title: 'Secure Payment', desc: 'Trusted checkout' },
    { icon: '🎧', title: '24/7 Support', desc: 'Always here for you' },
]);

const featuredProducts = ref([
    {
        id: 1,
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
        id: 2,
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
        id: 3,
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
        id: 4,
        name: 'Artisan Sourdough Loaf',
        code: 'BAK-003',
        price: 5.5,
        image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff',
        rating: 4.6,
        reviews: 67,
        inStock: false,
    },
]);

let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % promotions.value.length;
    }, 5000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <Head title="Home" />

    <UserLayout>
        <div class="pb-10">
            <section class="container mx-auto px-4 pt-6">
                <div class="relative overflow-hidden rounded-[2rem] bg-gradient-soft border border-border/60 shadow-card">
                    <div class="grid md:grid-cols-2 min-h-[420px] lg:min-h-[500px]">
                        <div class="relative z-10 p-8 md:p-14 flex flex-col justify-center">
                            <span class="inline-flex w-fit items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wider">
                                ✨ {{ currentPromotion.accent }}
                            </span>

                            <h1 class="mt-5 text-4xl md:text-5xl lg:text-6xl font-bold leading-[1.05]">
                                {{ currentPromotion.title }}
                            </h1>

                            <p class="mt-4 text-base md:text-lg text-muted-foreground max-w-md">
                                {{ currentPromotion.subtitle }}
                            </p>

                            <div class="mt-7 flex items-center gap-3">
                                <a
                                    :href="route('shop')"
                                    class="rounded-full bg-primary px-7 py-3 text-primary-foreground shadow-glow hover:bg-primary/90 transition-colors"
                                >
                                    Shop Now →
                                </a>

                                <a
                                    :href="route('categories')"
                                    class="rounded-full border border-border px-7 py-3 hover:bg-secondary transition-colors"
                                >
                                    Browse Categories
                                </a>
                            </div>
                        </div>

                        <div class="relative">
                            <img
                                :src="currentPromotion.image"
                                :alt="currentPromotion.title"
                                class="absolute inset-0 h-full w-full object-cover"
                            />

                            <div class="absolute inset-0 bg-gradient-to-r from-background via-background/40 to-transparent" />
                        </div>
                    </div>

                    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2">
                        <button
                            v-for="(item, index) in promotions"
                            :key="index"
                            type="button"
                            class="h-2 rounded-full transition-all"
                            :class="currentIndex === index ? 'w-8 bg-primary' : 'w-2 bg-foreground/20'"
                            :aria-label="`Go to slide ${index + 1}`"
                            @click="currentIndex = index"
                        />
                    </div>
                </div>
            </section>

            <section class="container mx-auto px-4 mt-10">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="flex items-center gap-4 p-5 rounded-2xl bg-card border border-border/60 shadow-soft"
                    >
                        <div class="h-12 w-12 rounded-2xl bg-primary/10 text-primary grid place-items-center shrink-0">
                            {{ feature.icon }}
                        </div>

                        <div>
                            <p class="font-semibold text-sm">{{ feature.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ feature.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="container mx-auto px-4 mt-16">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">
                            Featured Categories
                        </p>
                        <h2 class="text-2xl md:text-3xl font-bold mt-1">Shop by Category</h2>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a
                        v-for="category in categories"
                        :key="category.id"
                        :href="route('categories')"
                        class="group block"
                    >
                        <div class="aspect-square rounded-3xl overflow-hidden bg-gradient-to-br from-primary-soft to-secondary relative border border-border/60">
                            <img
                                :src="category.image"
                                :alt="category.name"
                                class="absolute inset-0 h-full w-full object-cover opacity-60"
                            />

                            <div class="absolute inset-0 bg-gradient-to-t from-background/95 via-background/30 to-transparent" />

                            <div class="absolute bottom-0 inset-x-0 p-3">
                                <div class="text-2xl mb-1">{{ category.icon }}</div>
                                <p class="text-sm font-semibold">{{ category.name }}</p>
                                <p class="text-[10px] text-muted-foreground">{{ category.count }} items</p>
                            </div>
                        </div>
                    </a>
                </div>
            </section>

            <section class="container mx-auto px-4 mt-16">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Trending Now</p>
                        <h2 class="text-2xl md:text-3xl font-bold mt-1">Popular Products</h2>
                    </div>

                    <a :href="route('shop')" class="text-sm font-medium text-primary hover:underline">
                        View all →
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <ProductCard
                        v-for="product in featuredProducts"
                        :key="product.id"
                        :product="product"
                    />
                </div>
            </section>

            <section class="container mx-auto px-4 mt-20">
                <div class="rounded-[2rem] bg-gradient-brand p-10 md:p-14 text-primary-foreground relative overflow-hidden">
                    <div class="relative max-w-2xl">
                        <h2 class="text-3xl md:text-4xl font-bold">Join the CamboMart Family</h2>
                        <p class="mt-3 opacity-90">Get exclusive deals and fresh-arrival alerts.</p>

                        <div class="mt-6 flex flex-col sm:flex-row gap-2 max-w-md">
                            <input
                                type="email"
                                placeholder="Enter your email"
                                class="flex-1 h-12 px-5 rounded-full text-foreground outline-none"
                            />
                            <button type="button" class="rounded-full bg-foreground text-background px-7 py-3 font-medium hover:opacity-90 transition-opacity">
                                Subscribe
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </UserLayout>
</template>
