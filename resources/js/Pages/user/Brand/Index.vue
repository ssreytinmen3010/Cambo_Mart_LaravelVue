<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import ProductCard from '@/Components/User/ProductCard.vue';
import { useStore } from '@/composables/useStore';

const props = defineProps({
    brands: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
});

const page = usePage();
const { loadMyRatings } = useStore();

const brands = computed(() =>
    (props.brands || []).map((brand) => ({
        ...brand,
        id: Number(brand.id),
        logo: '🏷️',
    })),
);

const products = computed(() =>
    (props.products || []).map((product) => ({
        id: product.id,
        brand: Number(product.brand_id),
        name: product.name,
        code: product.product_code,
        price: (() => {
            const basePrice = Number(product.price ?? 0);
            if (product.promotion?.promo_type === 'PERCENTAGE') {
                const discount = Number(product.promotion.discount_value ?? 0);
                return Math.max(0, Number((basePrice * (1 - discount / 100)).toFixed(2)));
            }
            return basePrice;
        })(),
        oldPrice: product.promotion?.promo_type === 'PERCENTAGE' ? Number(product.price ?? 0) : null,
        image: product.image,
        rating: Number(product.rating ?? 0),
        reviews: Number(product.reviews_count ?? 0),
        badge:
            product.promotion?.promo_type === 'PERCENTAGE'
                ? 'Sale'
                : product.promotion?.promo_type === 'BOGO'
                  ? 'BOGO'
                  : product.status_stock || null,
        inStock: Number(product.stock ?? 0) > 0,
    })),
);

const activeBrandId = ref(brands.value[0]?.id ?? null);

const filteredProducts = computed(() =>
    products.value.filter((product) => (!activeBrandId.value ? true : product.brand === activeBrandId.value)),
);

const activeBrand = computed(() => brands.value.find((brand) => brand.id === activeBrandId.value));

onMounted(() => {
    if (!page.props.auth?.user) return;
    const ids = (props.products || []).map((p) => Number(p.id)).filter(Boolean);
    loadMyRatings(ids);
});
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
                    <img
                        v-if="brand.image"
                        :src="brand.image"
                        :alt="brand.name"
                        class="h-10 w-10 object-contain"
                        loading="lazy"
                    />
                    <div v-else class="text-3xl">{{ brand.logo }}</div>
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
