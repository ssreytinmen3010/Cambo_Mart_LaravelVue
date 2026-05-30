<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserLayout from "@/Layouts/UserLayout.vue";
import ProductCard from "@/Components/User/ProductCard.vue";
import { useStore } from "@/composables/useStore";

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
});

const currentIndex = ref(0);
const page = usePage();
const { loadMyRatings } = useStore();
const storeName = computed(
    () => page.props.appSettings?.store_name ?? "CamboMart",
);

const promotions = ref([
    {
        title: "Fresh Organic Vegetables",
        subtitle: "Premium Cambodian farm products delivered fresh.",
        accent: "Natural Fresh",
        image: "https://images.unsplash.com/photo-1542838132-92c53300491e",
    },
    {
        title: "Healthy Fruits Collection",
        subtitle: "Sweet and fresh seasonal fruits.",
        accent: "Best Seller",
        image: "https://images.unsplash.com/photo-1619566636858-adf3ef46400b",
    },
]);

const currentPromotion = computed(() => promotions.value[currentIndex.value]);

const categories = computed(() =>
    (props.categories || []).map((category) => ({
        ...category,
        // UI expects `count` + `icon`
        qty_item: Number(category.qty_item ?? 0),
        icon: "🛍️",
    })),
);

const features = ref([
    { icon: "🚚", title: "Free Delivery", desc: "On orders over $25" },
    { icon: "🌿", title: "100% Natural", desc: "Locally sourced quality" },
    { icon: "🛡️", title: "Secure Payment", desc: "Trusted checkout" },
    { icon: "🎧", title: "24/7 Support", desc: "Always here for you" },
]);

const brands = computed(() =>
    (props.brands || []).slice(0, 8).map((brand) => ({
        id: Number(brand.id),
        name: brand.name,
        image: brand.image,
    })),
);

const featuredProducts = computed(() =>
    (props.products || []).map((product) => ({
        id: product.id,
        name: product.name,
        image: product.image,
        code: product.product_code,
        price: (() => {
            const basePrice = Number(product.price ?? 0);
            if (product.promotion?.promo_type === "PERCENTAGE") {
                const discount = Number(product.promotion.discount_value ?? 0);
                return Math.max(
                    0,
                    Number((basePrice * (1 - discount / 100)).toFixed(2)),
                );
            }
            return basePrice;
        })(),
        oldPrice:
            product.promotion?.promo_type === "PERCENTAGE"
                ? Number(product.price ?? 0)
                : null,
        badge:
            product.promotion?.promo_type === "PERCENTAGE"
                ? "Sale"
                : product.promotion?.promo_type === "BOGO"
                  ? "BOGO"
                  : product.status_stock || null,
        inStock: Number(product.stock ?? 0) > 0,
        rating: Number(product.rating ?? 0),
        reviews: Number(product.reviews_count ?? 0),
    })),
);

let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % promotions.value.length;
    }, 5000);

    if (page.props.auth?.user) {
        const ids = (props.products || [])
            .map((p) => Number(p.id))
            .filter(Boolean);
        loadMyRatings(ids);
    }
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
                <div
                    class="relative overflow-hidden rounded-[2rem] bg-gradient-soft border border-border/60 shadow-card"
                >
                    <div
                        class="grid md:grid-cols-2 min-h-[420px] lg:min-h-[500px]"
                    >
                        <div
                            class="relative z-10 p-8 md:p-14 flex flex-col justify-center"
                        >
                            <span
                                class="inline-flex w-fit items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wider"
                            >
                                ✨ {{ currentPromotion.accent }}
                            </span>

                            <h1
                                class="mt-5 text-4xl md:text-5xl lg:text-6xl font-bold leading-[1.05]"
                            >
                                {{ currentPromotion.title }}
                            </h1>

                            <p
                                class="mt-4 text-base md:text-lg text-muted-foreground max-w-md"
                            >
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

                            <div
                                class="absolute inset-0 bg-gradient-to-r from-background via-background/40 to-transparent"
                            />
                        </div>
                    </div>

                    <div
                        class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2"
                    >
                        <button
                            v-for="(item, index) in promotions"
                            :key="index"
                            type="button"
                            class="h-2 rounded-full transition-all"
                            :class="
                                currentIndex === index
                                    ? 'w-8 bg-primary'
                                    : 'w-2 bg-foreground/20'
                            "
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
                        <div
                            class="h-12 w-12 rounded-2xl bg-primary/10 text-primary grid place-items-center shrink-0"
                        >
                            {{ feature.icon }}
                        </div>

                        <div>
                            <p class="font-semibold text-sm">
                                {{ feature.title }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ feature.desc }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="container mx-auto px-4 mt-16">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <p
                            class="text-[11px] font-semibold uppercase tracking-[0.25em] text-primary"
                        >
                            Featured Categories
                        </p>
                        <h2 class="mt-1 text-xl md:text-2xl font-bold">
                            Shop by Category
                        </h2>
                    </div>

                    <a
                        :href="route('categories')"
                        class="shrink-0 text-sm font-semibold text-primary hover:underline"
                    >
                        View all →
                    </a>
                </div>

                <div class="flex gap-3 overflow-x-auto pb-2 pr-1">
                    <a
                        v-for="category in categories"
                        :key="category.id"
                        :href="route('categories')"
                        class="group block shrink-0 w-[160px] sm:w-[180px]"
                    >
                        <div
                            class="relative h-[132px] overflow-hidden rounded-2xl border border-border/60 bg-gradient-to-br from-primary-soft to-secondary"
                        >
                            <img
                                v-if="category.image"
                                :src="category.image"
                                :alt="category.name"
                                class="absolute inset-0 h-full w-full object-cover opacity-60"
                                loading="lazy"
                            />

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-background/95 via-background/30 to-transparent"
                            />

                            <div class="absolute bottom-0 inset-x-0 p-3">
                                <!-- <div class="text-2xl mb-1">{{ category.icon }}</div> -->
                                <p class="text-sm font-semibold leading-tight">
                                    {{ category.name }}
                                </p>
                                <p class="text-[10px] text-muted-foreground">
                                    {{ category.qty_item }} Total Product
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </section>

            <section class="container mx-auto px-4 mt-16">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-primary"
                        >
                            Trending Now
                        </p>
                        <h2 class="text-2xl md:text-3xl font-bold mt-1">
                            Popular Products
                        </h2>
                    </div>

                    <a
                        :href="route('shop')"
                        class="text-sm font-medium text-primary hover:underline"
                    >
                        View all →
                    </a>
                </div>

                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                >
                    <ProductCard
                        v-for="product in featuredProducts"
                        :key="product.id"
                        :product="product"
                    />
                </div>
            </section>
            <section v-if="brands.length" class="container mx-auto px-4 mt-16">
                <div class="mb-3 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-primary">
                            Brand
                        </p>
                        <h2 class="mt-0.5 text-lg font-bold md:text-xl">
                            Shop by Brand
                        </h2>
                    </div>

                    <a
                        :href="route('brand')"
                        class="shrink-0 text-xs font-semibold text-primary hover:underline sm:text-sm"
                    >
                        View all →
                    </a>
                </div>

                <div class="grid grid-cols-4 gap-2 sm:grid-cols-8">
                    <a
                        v-for="brand in brands"
                        :key="brand.id"
                        :href="route('brand')"
                        class="group flex aspect-square flex-col items-center justify-center gap-1 rounded-xl border border-border/60 bg-card p-2 transition-all hover:border-primary/40 hover:shadow-soft"
                    >
                        <img
                            v-if="brand.image"
                            :src="brand.image"
                            :alt="brand.name"
                            class="h-7 w-7 object-contain sm:h-8 sm:w-8"
                            loading="lazy"
                        />
                        <span
                            v-else
                            class="grid h-7 w-7 place-items-center rounded-lg bg-primary/10 text-[10px] font-bold text-primary sm:h-8 sm:w-8 sm:text-xs"
                        >
                            {{ brand.name?.charAt(0) }}
                        </span>
                        <p
                            class="line-clamp-1 w-full text-center text-[9px] font-medium text-muted-foreground group-hover:text-foreground sm:text-[10px]"
                        >
                            {{ brand.name }}
                        </p>
                    </a>
                </div>
            </section>

            <section class="container mx-auto px-4 mt-20">
                <div
                    class="rounded-[2rem] bg-gradient-brand p-10 md:p-14 text-primary-foreground relative overflow-hidden"
                >
                    <div class="relative max-w-2xl">
                        <h2 class="text-3xl md:text-4xl font-bold">
                            Join the {{ storeName }} Family
                        </h2>
                        <p class="mt-3 opacity-90">
                            Get exclusive deals and fresh-arrival alerts.
                        </p>

                        <div
                            class="mt-6 flex flex-col sm:flex-row gap-2 max-w-md"
                        >
                            <input
                                type="email"
                                placeholder="Enter your email"
                                class="flex-1 h-12 px-5 rounded-full text-foreground outline-none"
                            />
                            <button
                                type="button"
                                class="rounded-full bg-foreground text-background px-7 py-3 font-medium hover:opacity-90 transition-opacity"
                            >
                                Subscribe
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </UserLayout>
</template>
