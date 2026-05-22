<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import QuickViewModal from '@/Components/User/QuickViewModal.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const quickOpen = ref(false);
const wishlist = ref([]);

const wished = computed(() => wishlist.value.includes(props.product.id));

const discount = computed(() => {
    if (!props.product.oldPrice) return 0;
    return Math.round((1 - props.product.price / props.product.oldPrice) * 100);
});

function addToCart(product) {
    console.log('Add to cart:', product);
}

function toggleWishlist(id) {
    if (wishlist.value.includes(id)) {
        wishlist.value = wishlist.value.filter((item) => item !== id);
    } else {
        wishlist.value.push(id);
    }
}
</script>

<template>
    <div>
        <div
            class="group relative bg-card rounded-3xl overflow-hidden border border-border/60 hover:border-primary/30 shadow-soft hover:shadow-hover transition-all duration-300 hover:-translate-y-1"
        >
            <div class="relative aspect-square overflow-hidden bg-secondary/50">
                <Link :href="route('shop')">
                    <img
                        :src="product.image"
                        :alt="product.name"
                        loading="lazy"
                        class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-700"
                    />
                </Link>

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

                <div
                    class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all"
                >
                    <button
                        type="button"
                        :class="[
                            'h-9 w-9 grid place-items-center rounded-full backdrop-blur shadow-soft transition-colors',
                            wished
                                ? 'bg-destructive text-destructive-foreground'
                                : 'bg-background/90 hover:bg-primary hover:text-primary-foreground',
                        ]"
                        @click="toggleWishlist(product.id)"
                    >
                        ❤️
                    </button>

                    <button
                        type="button"
                        class="h-9 w-9 grid place-items-center rounded-full bg-background/90 backdrop-blur shadow-soft hover:bg-primary hover:text-primary-foreground transition-colors"
                        @click="quickOpen = true"
                    >
                        👁️
                    </button>
                </div>

                <div
                    v-if="!product.inStock"
                    class="absolute inset-0 bg-background/70 backdrop-blur-sm grid place-items-center"
                >
                    <span class="px-3 py-1 rounded-full bg-foreground text-background text-xs font-semibold">
                        Out of stock
                    </span>
                </div>

                <button
                    type="button"
                    :disabled="!product.inStock"
                    class="absolute bottom-3 left-3 right-3 h-10 rounded-full bg-foreground text-background text-sm font-medium flex items-center justify-center gap-2 translate-y-14 group-hover:translate-y-0 transition-transform duration-300 hover:bg-primary disabled:bg-muted disabled:text-muted-foreground disabled:cursor-not-allowed"
                    @click="addToCart(product)"
                >
                    🛒 Add to cart
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

                <Link
                    :href="route('shop')"
                    class="block font-semibold text-sm leading-snug line-clamp-2 hover:text-primary transition-colors min-h-[2.5rem]"
                >
                    {{ product.name }}
                </Link>

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
                    <span class="text-muted-foreground">({{ product.reviews }})</span>
                </div>

                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-lg font-bold text-foreground">${{ Number(product.price).toFixed(2) }}</span>
                    <span v-if="product.oldPrice" class="text-xs text-muted-foreground line-through">
                        ${{ Number(product.oldPrice).toFixed(2) }}
                    </span>
                </div>
            </div>
        </div>

        <QuickViewModal :product="product" :open="quickOpen" @close="quickOpen = false" />
    </div>
</template>
