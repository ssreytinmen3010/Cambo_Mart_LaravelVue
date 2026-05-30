<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import ProductCard from '@/Components/User/ProductCard.vue';
import { useStore } from '@/composables/useStore';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
});

const page = usePage();
const { loadMyRatings } = useStore();

const categories = computed(() =>
    (props.categories || []).map((category) => ({
        ...category,
        id: Number(category.id),
        count: Number(category.qty_item ?? 0),
    })),
);

const brands = computed(() =>
    (props.brands || []).map((brand) => ({
        ...brand,
        id: Number(brand.id),
    })),
);

const products = computed(() =>
    (props.products || []).map((product) => ({
        id: product.id,
        category: Number(product.category_id),
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
        badge: product.promotion?.promo_type === 'PERCENTAGE' ? 'Sale' : product.status_stock || null,
        inStock: Number(product.stock ?? 0) > 0,
    })),
);

const activeCategoryId = ref(categories.value[0]?.id ?? null);
const activeBrandId = ref(null);

const filteredProducts = computed(() =>
    products.value.filter((product) => {
        const matchesCategory = !activeCategoryId.value || product.category === activeCategoryId.value;
        const matchesBrand = !activeBrandId.value || product.brand === activeBrandId.value;
        return matchesCategory && matchesBrand;
    }),
);

const activeCategory = computed(() => categories.value.find((category) => category.id === activeCategoryId.value));

onMounted(() => {
    if (!page.props.auth?.user) return;
    const ids = (props.products || []).map((p) => Number(p.id)).filter(Boolean);
    loadMyRatings(ids);
});
</script>

<template>
    <Head>
        <title>Categories - CamboMart</title>
        <meta
            name="description"
            content="Browse all CamboMart product categories from fresh fruit and vegetables to bakery, dairy, and household."
        />
        <meta property="og:title" content="Categories - CamboMart" />
        <meta property="og:description" content="Browse all CamboMart product categories." />
    </Head>

    <UserLayout>
        <div class="container mx-auto px-4 py-8">
            <p class="text-xs text-muted-foreground">
                <Link href="/" class="hover:text-primary">Home</Link> / Categories
            </p>

            <h1 class="mt-2 text-3xl font-bold md:text-4xl">Shop by category</h1>
            <p class="mt-1 text-muted-foreground">
                Find exactly what you need across {{ categories.length }} curated collections.
            </p>

            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8">
                <button
                    v-for="c in categories"
                    :key="c.id"
                    type="button"
                    class="group block overflow-hidden rounded-3xl border text-left transition-all"
                    :class="
                        activeCategoryId === c.id
                            ? 'border-primary shadow-hover ring-2 ring-primary/20'
                            : 'border-border/60 hover:border-primary/40 hover:shadow-card'
                    "
                    @click="activeCategoryId = c.id"
                >
                    <div class="relative aspect-square bg-gradient-to-br from-primary-soft to-secondary">
                        <img
                            v-if="c.image"
                            :src="c.image"
                            :alt="c.name"
                            class="absolute inset-0 h-full w-full object-cover opacity-90 transition-transform duration-500 group-hover:scale-110"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-background/95 via-background/30 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-3">
                            <p class="text-sm font-semibold">{{ c.name }}</p>
                            <p class="text-[10px] text-muted-foreground">{{ c.count }} items</p>
                        </div>
                    </div>
                </button>
            </div>

            <div class="mt-10 grid gap-8 lg:grid-cols-[240px_1fr]">
                <aside class="space-y-3">
                    <h3 class="text-sm font-semibold">Filter by brand</h3>

                    <button
                        type="button"
                        class="block w-full rounded-xl px-4 py-2.5 text-left text-sm transition-colors"
                        :class="activeBrandId === null ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary'"
                        @click="activeBrandId = null"
                    >
                        All brands
                    </button>

                    <button
                        v-for="b in brands"
                        :key="b.id"
                        type="button"
                        class="flex w-full items-center gap-2 rounded-xl px-4 py-2.5 text-left text-sm transition-colors"
                        :class="activeBrandId === b.id ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary'"
                        @click="activeBrandId = Number(b.id)"
                    >
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border/60 bg-muted">
                            <img
                                v-if="b.image"
                                :src="b.image"
                                :alt="b.name"
                                class="h-full w-full object-cover"
                            />
                        </span>
                        <span class="truncate">{{ b.name }}</span>
                    </button>
                </aside>

                <div>
                    <h2 class="mb-4 text-xl font-bold">
                        {{ activeCategory?.name }} ·
                        <span class="font-normal text-muted-foreground">
                            {{ filteredProducts.length }} products
                        </span>
                    </h2>

                    <div v-if="filteredProducts.length === 0" class="rounded-3xl border border-dashed py-20 text-center">
                        <p class="text-muted-foreground">No products in this combination.</p>
                    </div>

                    <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                        <ProductCard v-for="p in filteredProducts" :key="p.id" :product="p" />
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
