<script setup>
import { ref, computed } from 'vue';
import { ShoppingCart } from 'lucide-vue-next';
import QuickViewModal from '@/Components/User/QuickViewModal.vue';
import { useStore } from '@/composables/useStore';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const { addToCart, isInWishlist, toggleWishlist } = useStore();
const detailOpen = ref(false);

const wished = computed(() => isInWishlist(props.product.id));

const discount = computed(() => {
    if (!props.product.oldPrice) return 0;
    return Math.round((1 - props.product.price / props.product.oldPrice) * 100);
});

function openDetail() {
    detailOpen.value = true;
}
</script>

<template>
    <div>
        <div
            class="product-card group relative bg-card rounded-3xl overflow-hidden border border-border/60 hover:border-primary/30 shadow-soft hover:shadow-hover transition-all duration-300 hover:-translate-y-1"
        >
            <div class="product-card-image relative aspect-square overflow-hidden bg-secondary/50">
                <img
                    :src="product.image"
                    :alt="product.name"
                    loading="lazy"
                    class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-700"
                />

                <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                    <span
                        v-if="product.badge"
                        :class="[
                            'px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full',
                            product.badge === 'Sale'
                                ? 'bg-destructive text-destructive-foreground'
                                : product.badge === 'New'
                                  ? 'bg-foreground text-background'
                                  : product.badge === 'Hot'
                                    ? 'bg-warning text-foreground'
                                    : 'bg-primary text-primary-foreground',
                        ]"
                    >
                        {{ product.badge }}
                    </span>

                    <span
                        v-if="discount > 0"
                        class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-destructive/95 text-destructive-foreground"
                    >
                        -{{ discount }}%
                    </span>
                </div>

                <div v-if="product.inStock" class="absolute top-3 right-3 z-20 flex flex-col gap-2 pointer-events-auto">
                    <button
                        type="button"
                        :title="wished ? 'Remove from wishlist' : 'Add to wishlist'"
                        :class="[
                            'product-card-action h-9 w-9 grid place-items-center rounded-full border border-border/60 bg-white/95 text-foreground shadow-md backdrop-blur-sm transition-all duration-300',
                            'opacity-100 group-hover:scale-110',
                            wished
                                ? '!bg-destructive !text-white !border-destructive'
                                : 'hover:bg-primary hover:text-primary-foreground hover:border-primary',
                        ]"
                        @click.stop="toggleWishlist(product.id)"
                    >
                        <span class="text-base leading-none" aria-hidden="true">❤️</span>
                    </button>

                    <button
                        type="button"
                        title="View detail"
                        class="product-card-action h-9 w-9 grid place-items-center rounded-full border border-border/60 bg-white/95 text-foreground shadow-md backdrop-blur-sm transition-all duration-300 opacity-100 group-hover:scale-110 hover:bg-primary hover:text-primary-foreground hover:border-primary"
                        @click.stop="openDetail"
                    >
                        <span class="text-base leading-none" aria-hidden="true">👁️</span>
                    </button>
                </div>

                <div
                    v-if="!product.inStock"
                    class="absolute inset-0 bg-background/70 backdrop-blur-sm grid place-items-center z-10"
                >
                    <span class="px-3 py-1 rounded-full bg-foreground text-background text-xs font-semibold">
                        Out of stock
                    </span>
                </div>

                <button
                    type="button"
                    :disabled="!product.inStock"
                    class="product-card-cart absolute bottom-3 left-3 right-3 z-20 h-10 rounded-full bg-foreground text-background text-sm font-medium flex items-center justify-center gap-2 translate-y-[120%] group-hover:translate-y-0 transition-transform duration-300 hover:bg-primary disabled:bg-muted disabled:text-muted-foreground disabled:cursor-not-allowed pointer-events-auto"
                    @click.stop="addToCart(product)"
                >
                    <ShoppingCart class="h-4 w-4" />
                    Add to cart
                </button>
            </div>

            <div class="p-4">
                <div class="flex items-center justify-between text-[11px] text-muted-foreground mb-1.5">
                    <span class="font-mono">{{ product.code }}</span>
                    <span :class="['flex items-center gap-1', product.inStock ? 'text-primary' : 'text-destructive']">
                        <span :class="['h-1.5 w-1.5 rounded-full', product.inStock ? 'bg-primary' : 'bg-destructive']" />
                        {{ product.inStock ? 'In stock' : 'Sold out' }}
                    </span>
                </div>

                <button
                    type="button"
                    class="block w-full text-left font-semibold text-sm leading-snug line-clamp-2 hover:text-primary transition-colors min-h-[2.5rem]"
                    @click="openDetail"
                >
                    {{ product.name }}
                </button>

                <div class="mt-2 flex items-center gap-1 text-xs">
                    <div class="flex items-center">
                        <span
                            v-for="i in 5"
                            :key="i"
                            :class="i <= Math.round(product.rating) ? 'text-warning' : 'text-muted'"
                        >
                            ⭐
                        </span>
                    </div>
                    <span class="text-muted-foreground">
                        {{ Number(product.rating).toFixed(1) }} · {{ product.reviews }} reviews
                    </span>
                </div>

                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-lg font-bold text-foreground">${{ Number(product.price).toFixed(2) }}</span>
                    <span v-if="product.oldPrice" class="text-xs text-muted-foreground line-through">
                        ${{ Number(product.oldPrice).toFixed(2) }}
                    </span>
                </div>
            </div>
        </div>

        <QuickViewModal :product="product" :open="detailOpen" @close="detailOpen = false" />
    </div>
</template>

<style scoped>
.product-card-image:hover .product-card-action,
.product-card:hover .product-card-action {
    opacity: 1;
    transform: translateX(0);
}

.product-card-image:hover .product-card-cart,
.product-card:hover .product-card-cart {
    transform: translateY(0);
}

/* Always show action icons (hover-only was hiding them on touch / some browsers) */
.product-card-action {
    opacity: 1 !important;
    transform: translateX(0) !important;
}
</style>
