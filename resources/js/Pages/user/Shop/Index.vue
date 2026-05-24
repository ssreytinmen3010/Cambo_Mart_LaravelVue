<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Filter, Star, X } from 'lucide-vue-next';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';
import ProductCard from '@/Components/User/ProductCard.vue';

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

function toggleInArray(list, id) {
    const index = list.indexOf(id);
    if (index > -1) {
        list.splice(index, 1);
    } else {
        list.push(id);
    }
}

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
        logo: '🏷️',
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
        price: Number(product.price ?? 0),
        oldPrice: null,
        badge: product.status_stock || null,
        inStock: Number(product.stock ?? 0) > 0,
        rating: 5,
        reviews: 0,
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

                    <div class="bg-card rounded-2xl p-4 border border-border/60">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Category</p>
                        <div class="space-y-2">
                            <label v-for="c in categories" :key="c.id" class="flex items-center gap-2.5 cursor-pointer text-sm">
                                <input
                                    type="checkbox"
                                    :checked="activeCats.includes(c.id)"
                                    class="h-4 w-4 rounded accent-primary"
                                    @change="toggleInArray(activeCats, c.id)"
                                />
                                <span>{{ c.name }}</span>
                                <span class="ml-auto text-xs text-muted-foreground">{{ c.count }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-card rounded-2xl p-4 border border-border/60">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Brand</p>
                        <div class="space-y-2">
                            <label v-for="b in brands" :key="b.id" class="flex items-center gap-2.5 cursor-pointer text-sm">
                                <input
                                    type="checkbox"
                                    :checked="activeBrands.includes(b.id)"
                                    class="h-4 w-4 rounded accent-primary"
                                    @change="toggleInArray(activeBrands, b.id)"
                                />
                                <span>{{ b.logo }} {{ b.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-card rounded-2xl p-4 border border-border/60 space-y-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Promotions</p>
                        <label class="flex items-center gap-2.5 text-sm cursor-pointer">
                            <input v-model="onSaleOnly" type="checkbox" class="h-4 w-4 rounded accent-primary" /> On sale
                        </label>
                        <label class="flex items-center gap-2.5 text-sm cursor-pointer">
                            <input v-model="inStockOnly" type="checkbox" class="h-4 w-4 rounded accent-primary" /> In stock only
                        </label>
                    </div>

                    <div class="bg-card rounded-2xl p-4 border border-border/60">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Price range</p>
                        <input v-model.number="maxPrice" type="range" min="3" max="60" class="w-full accent-primary" />
                        <div class="flex justify-between text-xs text-muted-foreground mt-1">
                            <span>$3</span>
                            <span class="font-semibold text-foreground">Up to ${{ maxPrice }}</span>
                        </div>
                    </div>

                    <div class="bg-card rounded-2xl p-4 border border-border/60">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Customer rating</p>
                        <div class="space-y-2">
                            <button
                                v-for="r in [4, 3, 2, 0]"
                                :key="r"
                                type="button"
                                class="w-full flex items-center gap-2 text-sm px-2 py-1.5 rounded-lg"
                                :class="minRating === r ? 'bg-primary/10 text-primary' : 'hover:bg-secondary'"
                                @click="minRating = r"
                            >
                                <div class="flex">
                                    <Star
                                        v-for="i in 5"
                                        :key="i"
                                        class="h-3.5 w-3.5"
                                        :class="i <= r ? 'fill-warning text-warning' : 'text-muted'"
                                    />
                                </div>
                                {{ r === 0 ? 'All ratings' : '& up' }}
                            </button>
                        </div>
                    </div>
                </aside>

                <div>
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                        <button
                            type="button"
                            class="lg:hidden inline-flex items-center gap-2 px-4 h-10 rounded-full border text-sm font-medium"
                            @click="filtersOpen = true"
                        >
                            <Filter class="h-4 w-4" /> Filters
                        </button>

                        <p class="text-sm text-muted-foreground">
                            <span class="font-semibold text-foreground">{{ filteredProducts.length }}</span> products
                        </p>

                        <select
                            v-model="sort"
                            class="h-10 rounded-full border px-4 text-sm bg-background outline-none focus:border-primary cursor-pointer"
                        >
                            <option value="newest">Newest</option>
                            <option value="low">Price: low to high</option>
                            <option value="high">Price: high to low</option>
                            <option value="name">Name: A–Z</option>
                        </select>
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

            <div v-if="filtersOpen" class="fixed inset-0 z-50 lg:hidden">
                <div class="absolute inset-0 bg-foreground/50" @click="filtersOpen = false" />
                <div class="absolute left-0 top-0 bottom-0 w-80 max-w-[85vw] bg-background p-6 overflow-y-auto">
                    <button
                        type="button"
                        class="absolute top-3 right-3 h-9 w-9 grid place-items-center rounded-full hover:bg-secondary"
                        @click="filtersOpen = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                    <p class="font-semibold mb-4">Filters</p>
                    <button type="button" class="text-xs text-primary mb-4" @click="resetFilters">Reset all</button>
                    <p class="text-sm text-muted-foreground">Use desktop view for full filter panel, or adjust sort above.</p>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
