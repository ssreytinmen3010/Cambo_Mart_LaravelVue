<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Filter, X, ChevronDown } from 'lucide-vue-next';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';
import ProductCard from '@/Components/User/ProductCard.vue';
import ShopFiltersPanel from '@/Components/User/ShopFiltersPanel.vue';
import { useStore } from '@/composables/useStore';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
});

const activeCats = ref([]);
const activeBrands = ref([]);
const inStockOnly = ref(false);
const onSaleOnly = ref(false);
const minRating = ref(0);
const maxPrice = ref(60);
const sort = ref('newest');
const filtersOpen = ref(false);

const sortOptions = [
    { value: 'newest', label: 'Newest' },
    { value: 'low', label: 'Price: low to high' },
    { value: 'high', label: 'Price: high to low' },
    { value: 'name', label: 'Name: A–Z' },
];

const page = usePage();
const { loadMyRatings } = useStore();

function resetFilters() {
    activeCats.value = [];
    activeBrands.value = [];
    inStockOnly.value = false;
    onSaleOnly.value = false;
    minRating.value = 0;
    maxPrice.value = 60;
}

const categories = computed(() =>
    (props.categories || []).map((category) => ({
        ...category,
        id: Number(category.id),
        count: Number(category.qty_item ?? 0),
    }))
);

const brands = computed(() =>
    (props.brands || []).map((brand) => ({
        ...brand,
        id: Number(brand.id),
    }))
);

const products = computed(() =>
    (props.products || []).map((product) => ({
        id: product.id,
        name: product.name,
        image: product.image,
        code: product.product_code,
        category: Number(product.category_id),
        brand: Number(product.brand_id),
        price: (() => {
            const basePrice = Number(product.price ?? 0);
            if (product.promotion?.promo_type === 'PERCENTAGE') {
                const discount = Number(product.promotion.discount_value ?? 0);
                return Math.max(0, Number((basePrice * (1 - discount / 100)).toFixed(2)));
            }
            return basePrice;
        })(),
        oldPrice: product.promotion?.promo_type === 'PERCENTAGE' ? Number(product.price ?? 0) : null,
        badge: product.promotion?.promo_type === 'PERCENTAGE' ? 'Sale' : product.status_stock || null,
        inStock: Number(product.stock ?? 0) > 0,
        rating: Number(product.rating ?? 0),
        reviews: Number(product.reviews_count ?? 0),
        createdAt: product.created_at,
    }))
);

const filteredProducts = computed(() => {
    let list = products.value.filter((p) => {
        if (activeCats.value.length && !activeCats.value.includes(p.category)) return false;
        if (activeBrands.value.length && !activeBrands.value.includes(p.brand)) return false;
        if (inStockOnly.value && !p.inStock) return false;
        if (onSaleOnly.value && !p.oldPrice) return false;
        if (p.rating < minRating.value) return false;
        if (p.price > maxPrice.value) return false;
        return true;
    });

    if (sort.value === 'newest') list = [...list].sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
    if (sort.value === 'low') list = [...list].sort((a, b) => a.price - b.price);
    if (sort.value === 'high') list = [...list].sort((a, b) => b.price - a.price);
    if (sort.value === 'name') list = [...list].sort((a, b) => a.name.localeCompare(b.name));

    return list;
});

watch(filtersOpen, (open) => {
    if (typeof document === 'undefined') return;
    document.body.style.overflow = open ? 'hidden' : '';
});

onMounted(() => {
    if (!page.props.auth?.user) return;
    const ids = (props.products || []).map((p) => Number(p.id)).filter(Boolean);
    loadMyRatings(ids);
});
</script>

<template>
    <Head title="Shop" />

    <UserLayout>
        <div class="container mx-auto px-4 py-8">
            <UserBreadcrumb :items="[{ label: 'Home', href: route('home') }, { label: 'Shop' }]" />

            <h1 class="mt-2 text-3xl md:text-4xl font-bold">Shop everything fresh</h1>
            <p class="mt-1 text-muted-foreground">Discover {{ products.length }} naturally sourced products from local brands.</p>

            <div class="mt-8 grid lg:grid-cols-[260px_1fr] gap-8">
                <aside class="hidden lg:block space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold flex items-center gap-2">
                            <Filter class="h-4 w-4" /> Filters
                        </h3>
                        <button type="button" class="text-xs text-primary hover:underline" @click="resetFilters">Reset all</button>
                    </div>

                    <ShopFiltersPanel
                        v-model:active-cats="activeCats"
                        v-model:active-brands="activeBrands"
                        v-model:in-stock-only="inStockOnly"
                        v-model:on-sale-only="onSaleOnly"
                        v-model:min-rating="minRating"
                        v-model:max-price="maxPrice"
                        :categories="categories"
                        :brands="brands"
                    />
                </aside>

                <div>
                    <div class="mb-5 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="lg:hidden inline-flex items-center gap-2 rounded-full border px-4 h-11 text-sm font-medium"
                                @click="filtersOpen = true"
                            >
                                <Filter class="h-4 w-4" /> Filters
                            </button>

                            <p class="text-sm text-muted-foreground">
                                <span class="font-semibold text-foreground">{{ filteredProducts.length }}</span> products
                            </p>
                        </div>

                        <label class="block w-full sm:max-w-xs sm:ml-auto">
                            <span class="mb-1.5 block text-xs font-medium text-muted-foreground">Sort by</span>
                            <div class="relative">
                                <select
                                    v-model="sort"
                                    class="shop-sort-select h-11 w-full appearance-none rounded-full border border-border bg-background pl-4 pr-10 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 cursor-pointer"
                                >
                                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <ChevronDown
                                    class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                    aria-hidden="true"
                                />
                            </div>
                        </label>
                    </div>

                    <div v-if="filteredProducts.length === 0" class="rounded-3xl border border-dashed py-20 text-center">
                        <p class="text-lg font-semibold">No products match your filters</p>
                        <p class="text-sm text-muted-foreground mt-1">Try resetting filters or widening your search.</p>
                        <button type="button" class="mt-5 rounded-full bg-primary px-6 py-2 text-primary-foreground" @click="resetFilters">
                            Reset filters
                        </button>
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <ProductCard v-for="p in filteredProducts" :key="p.id" :product="p" />
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="drawer-fade">
                <div v-if="filtersOpen" class="fixed inset-0 z-[200] lg:hidden">
                    <div
                        class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                        aria-hidden="true"
                        @click="filtersOpen = false"
                    />

                    <Transition name="drawer-slide" appear>
                        <aside
                            v-if="filtersOpen"
                            class="absolute left-0 top-0 z-10 flex h-full w-[min(20rem,88vw)] flex-col bg-background shadow-2xl"
                            role="dialog"
                            aria-modal="true"
                            aria-label="Filters"
                        >
                            <div class="flex shrink-0 items-center justify-between border-b border-border/60 px-4 py-4">
                                <h3 class="font-semibold flex items-center gap-2">
                                    <Filter class="h-4 w-4" /> Filters
                                </h3>
                                <button
                                    type="button"
                                    class="h-9 w-9 grid place-items-center rounded-full hover:bg-secondary"
                                    aria-label="Close filters"
                                    @click="filtersOpen = false"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="shrink-0 border-b border-border/60 px-4 py-3 space-y-3">
                                <label class="block w-full">
                                    <span class="mb-1.5 block text-xs font-medium text-muted-foreground">Sort by</span>
                                    <div class="relative">
                                        <select
                                            v-model="sort"
                                            class="shop-sort-select h-11 w-full appearance-none rounded-xl border border-border bg-background pl-4 pr-10 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10"
                                        >
                                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                            aria-hidden="true"
                                        />
                                    </div>
                                </label>

                                <div class="flex items-center justify-between gap-2">
                                    <button type="button" class="text-xs text-primary font-medium" @click="resetFilters">
                                        Reset all
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground"
                                        @click="filtersOpen = false"
                                    >
                                        Show {{ filteredProducts.length }} products
                                    </button>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto px-4 py-4">
                                <ShopFiltersPanel
                                    v-model:active-cats="activeCats"
                                    v-model:active-brands="activeBrands"
                                    v-model:in-stock-only="inStockOnly"
                                    v-model:on-sale-only="onSaleOnly"
                                    v-model:min-rating="minRating"
                                    v-model:max-price="maxPrice"
                                    :categories="categories"
                                    :brands="brands"
                                />
                            </div>
                        </aside>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </UserLayout>
</template>

<style scoped>
.drawer-fade-enter-active,
.drawer-fade-leave-active {
    transition: opacity 0.25s ease;
}

.drawer-fade-enter-from,
.drawer-fade-leave-to {
    opacity: 0;
}

.drawer-slide-enter-active,
.drawer-slide-leave-active {
    transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}

.drawer-slide-enter-from,
.drawer-slide-leave-to {
    transform: translateX(-100%);
}

.shop-sort-select {
    -webkit-appearance: none;
    appearance: none;
}

@supports (-webkit-touch-callout: none) {
    .shop-sort-select {
        font-size: 16px;
    }
}
</style>
