<template>
    <transition name="fade">
        <div v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">

            <div
                class="w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl">

                <div class="grid md:grid-cols-2">

                    <!-- Product Image -->
                    <div class="aspect-square bg-secondary/40">

                        <img :src="product.image"
                            :alt="product.name"
                            class="h-full w-full object-cover" />
                    </div>

                    <!-- Content -->
                    <div class="p-6 md:p-8 flex flex-col">

                        <!-- Top -->
                        <div class="flex items-center gap-2 text-xs">

                            <span class="font-mono text-muted-foreground">
                                {{ product.code }}
                            </span>

                            <span class="h-1 w-1 rounded-full bg-border"></span>

                            <span
                                :class="product.inStock
                                    ? 'text-primary font-medium'
                                    : 'text-destructive'">

                                {{ product.inStock ? 'In stock' : 'Out of stock' }}
                            </span>
                        </div>

                        <!-- Name -->
                        <h2 class="text-2xl font-bold mt-2">
                            {{ product.name }}
                        </h2>

                        <!-- Rating -->
                        <div class="mt-2 flex items-center gap-2">

                            <div class="flex">

                                <span v-for="i in 5"
                                    :key="i"
                                    :class="i <= Math.round(product.rating)
                                        ? 'text-warning'
                                        : 'text-muted'">

                                    ⭐
                                </span>
                            </div>

                            <span class="text-sm text-muted-foreground">
                                {{ Number(product.rating).toFixed(1) }}
                                ·
                                {{ product.reviews }} reviews
                            </span>
                        </div>

                        <!-- Price -->
                        <div class="mt-4 flex items-baseline gap-3">

                            <span class="text-3xl font-bold">
                                ${{ Number(product.price).toFixed(2) }}
                            </span>

                            <span v-if="product.oldPrice"
                                class="text-base text-muted-foreground line-through">

                                ${{ Number(product.oldPrice).toFixed(2) }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="mt-4 text-sm text-muted-foreground leading-relaxed">
                            {{ product.description }}
                        </p>

                        <!-- Actions -->
                        <div class="mt-6 flex items-center gap-3">

                            <!-- Quantity -->
                            <div class="flex items-center border rounded-full h-11">

                                <button
                                    class="h-11 w-11 grid place-items-center hover:bg-secondary rounded-l-full"
                                    @click="decreaseQty">

                                    ➖
                                </button>

                                <span class="w-10 text-center font-semibold">
                                    {{ qty }}
                                </span>

                                <button
                                    class="h-11 w-11 grid place-items-center hover:bg-secondary rounded-r-full"
                                    @click="qty++">

                                    ➕
                                </button>
                            </div>

                            <!-- Add To Cart -->
                            <button
                                :disabled="!product.inStock"
                                @click="handleAddToCart"
                                class="flex-1 rounded-full h-11 bg-primary text-white font-medium hover:bg-primary/90 disabled:bg-muted disabled:text-muted-foreground disabled:cursor-not-allowed flex items-center justify-center gap-2">

                                🛒 Add to cart
                            </button>

                            <!-- Wishlist -->
                            <button
                                @click="toggleWishlist(product.id)"
                                :class="[
                                    'h-11 w-11 grid place-items-center rounded-full border',
                                    wished
                                        ? 'bg-destructive text-destructive-foreground border-destructive'
                                        : 'hover:bg-secondary'
                                ]">

                                ❤️
                            </button>
                        </div>

                        <!-- Footer -->
                        <div
                            class="mt-6 pt-6 border-t text-xs text-muted-foreground space-y-1.5">

                            <p>🚚 Free delivery on orders over $25</p>

                            <p>🔄 7-day return policy</p>

                            <p>🌿 100% naturally sourced</p>
                        </div>

                        <!-- Close -->
                        <button
                            @click="$emit('close')"
                            class="mt-6 text-sm text-muted-foreground hover:text-foreground">

                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "QuickViewModal",

    props: {
        product: {
            type: Object,
            required: true,
        },

        open: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["close"],

    data() {
        return {
            qty: 1,
            wishlist: [],
        };
    },

    computed: {
        wished() {
            return this.wishlist.includes(this.product.id);
        },
    },

    methods: {
        decreaseQty() {
            this.qty = Math.max(1, this.qty - 1);
        },

        addToCart(product, qty) {
            console.log("Add to cart:", product, qty);
        },

        handleAddToCart() {
            this.addToCart(this.product, this.qty);
            this.$emit("close");
        },

        toggleWishlist(id) {
            if (this.wishlist.includes(id)) {
                this.wishlist = this.wishlist.filter(item => item !== id);
            } else {
                this.wishlist.push(id);
            }
        },
    },
};
</script>

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