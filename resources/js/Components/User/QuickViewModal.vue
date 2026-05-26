<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { X, Minus, Plus, ShoppingCart, Star } from 'lucide-vue-next';
import { useStore } from '@/composables/useStore';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();
const { addToCart, isInWishlist, toggleWishlist, rateProduct, myRatingsByProductId } = useStore();
const qty = ref(1);

const wished = computed(() => isInWishlist(props.product.id));

const description = computed(
    () =>
        props.product.description ??
        'Hand-picked, naturally sourced, and delivered fresh to your door. Premium quality you can trust from CamboMart.',
);

const discount = computed(() => {
    if (!props.product.oldPrice) return 0;
    return Math.round((1 - props.product.price / props.product.oldPrice) * 100);
});

const displayRating = computed(() => {
    // Only highlight the current user's own rating (not the average),
    // so new users see all-white stars until they rate.
    return Number(myRatingsByProductId[props.product.id] ?? 0);
});

const myRating = computed(() => Number(myRatingsByProductId[props.product.id] ?? 0));
const avgRatingText = computed(() => Number(props.product.rating ?? 0).toFixed(1));
const reviewsCount = computed(() => Number(props.product.reviews ?? 0));

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) qty.value = 1;
    },
);

function decreaseQty() {
    qty.value = Math.max(1, qty.value - 1);
}

function increaseQty() {
    qty.value += 1;
}

async function setRating(value) {
    try {
        if (!page.props.auth?.user) return router.visit(route('login'));
        await rateProduct(props.product.id, value);
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('Save rating failed:', e);
    }
}

async function handleAddToCart() {
    if (!props.product.inStock) return;
    try {
        await addToCart(props.product, qty.value);
        emit('close');
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('Add to cart failed:', e);
    }
}

async function handleWishlist() {
    try {
        if (!page.props.auth?.user) return router.visit(route('login'));
        await toggleWishlist(props.product.id);
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('Wishlist toggle failed:', e);
    }
}
</script>

<template>
    <transition name="fade">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8"
            @click.self="emit('close')"
        >
            <div class="relative w-full max-w-3xl overflow-hidden rounded-3xl bg-card border border-border/60 shadow-2xl">
                <button
                    type="button"
                    class="absolute top-4 right-4 z-10 h-9 w-9 grid place-items-center rounded-full bg-background/90 border border-border/60 hover:bg-secondary transition-colors"
                    aria-label="Close"
                    @click="emit('close')"
                >
                    <X class="h-4 w-4" />
                </button>

                <div class="grid md:grid-cols-2">
                    <div class="relative aspect-square bg-secondary/40">
                        <img :src="product.image" :alt="product.name" class="h-full w-full object-cover" />

                        <span
                            v-if="product.badge"
                            class="absolute top-4 left-4 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-primary text-primary-foreground"
                        >
                            {{ product.badge }}
                        </span>
                    </div>

                    <div class="p-6 md:p-8 flex flex-col max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center gap-2 text-xs flex-wrap">
                            <span class="font-mono text-muted-foreground">{{ product.code }}</span>
                            <span class="h-1 w-1 rounded-full bg-border" />
                            <span
                                class="flex items-center gap-1 font-medium"
                                :class="product.inStock ? 'text-primary' : 'text-destructive'"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full"
                                    :class="product.inStock ? 'bg-primary' : 'bg-destructive'"
                                />
                                {{ product.inStock ? 'In stock' : 'Out of stock' }}
                            </span>
                        </div>

                        <h2 class="text-2xl md:text-3xl font-bold mt-2 leading-tight">
                            {{ product.name }}
                        </h2>

                        <div class="mt-2 flex items-center gap-2 flex-wrap">
                            <div class="flex items-center">
                                <button
                                    v-for="i in 5"
                                    :key="i"
                                    type="button"
                                    class="leading-none"
                                    :class="i <= displayRating ? 'text-warning' : 'text-white/80 hover:text-white'"
                                    :title="`Rate ${i} star${i > 1 ? 's' : ''}`"
                                    @click.stop="setRating(i)"
                                >
                                    <Star
                                        class="h-4 w-4"
                                        :class="i <= displayRating ? 'fill-warning text-warning' : 'fill-transparent'"
                                    />
                                </button>
                            </div>
                            <span class="text-sm text-muted-foreground">
                                {{ avgRatingText }} ·
                                <span v-if="myRating > 0 && reviewsCount === 0">You rated {{ myRating }}/5</span>
                                <span v-else>{{ reviewsCount }} {{ reviewsCount === 1 ? 'review' : 'reviews' }}</span>
                            </span>
                        </div>

                        <div class="mt-4 flex items-baseline gap-3 flex-wrap">
                            <span class="text-3xl font-bold text-primary">
                                ${{ Number(product.price).toFixed(2) }}
                            </span>
                            <span v-if="product.oldPrice" class="text-base text-muted-foreground line-through">
                                ${{ Number(product.oldPrice).toFixed(2) }}
                            </span>
                            <span
                                v-if="discount > 0"
                                class="text-xs font-bold px-2 py-0.5 rounded-full bg-destructive/10 text-destructive"
                            >
                                -{{ discount }}%
                            </span>
                        </div>

                        <p class="mt-4 text-sm text-muted-foreground leading-relaxed">
                            {{ description }}
                        </p>

                        <div class="mt-6 flex items-center gap-3">
                            <div class="flex items-center border border-border rounded-full h-11">
                                <button
                                    type="button"
                                    class="h-11 w-11 grid place-items-center hover:bg-secondary rounded-l-full transition-colors"
                                    :disabled="qty <= 1"
                                    @click="decreaseQty"
                                >
                                    <Minus class="h-4 w-4" />
                                </button>
                                <span class="w-10 text-center font-semibold text-sm">{{ qty }}</span>
                                <button
                                    type="button"
                                    class="h-11 w-11 grid place-items-center hover:bg-secondary rounded-r-full transition-colors"
                                    @click="increaseQty"
                                >
                                    <Plus class="h-4 w-4" />
                                </button>
                            </div>

                            <button
                                type="button"
                                :disabled="!product.inStock"
                                title="Add to cart"
                                class="h-11 w-11 grid place-items-center rounded-full bg-primary text-primary-foreground shadow-glow hover:bg-primary/90 disabled:bg-muted disabled:text-muted-foreground disabled:cursor-not-allowed transition-colors"
                                @click="handleAddToCart"
                            >
                                <ShoppingCart class="h-5 w-5 shrink-0" stroke-width="2" />
                            </button>

                            <button
                                type="button"
                                :title="wished ? 'Remove from wishlist' : 'Add to wishlist'"
                                :class="[
                                    'h-11 w-11 grid place-items-center rounded-full border border-border/60 bg-white shadow-md transition-colors text-lg',
                                    wished
                                        ? '!bg-destructive !text-white !border-destructive'
                                        : 'hover:bg-primary hover:border-primary',
                                ]"
                                @click="handleWishlist"
                            >
                                <span aria-hidden="true">❤️</span>
                            </button>
                        </div>

                        <div class="mt-6 pt-6 border-t border-border/60 text-xs text-muted-foreground space-y-1.5">
                            <p>🚚 Free delivery on orders over $25</p>
                            <p>🔄 7-day return policy</p>
                            <p>🌿 100% naturally sourced</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
