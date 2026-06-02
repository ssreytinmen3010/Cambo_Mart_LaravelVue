<script setup>
import { computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ShoppingBag, Minus, Plus, Trash2, ArrowRight } from 'lucide-vue-next';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';
import { useStore } from '@/composables/useStore';

const { cart, removeFromCart, updateQty, clearCart, cartSubtotal, cartDiscount, cartTotal, ensureCartLoaded } = useStore();
const page = usePage();

onMounted(() => {
    ensureCartLoaded();
});

const total = computed(() => cartTotal.value);
const deliveryFeePerKg = computed(() => Number(page.props.delivery?.fee_amount_per ?? 0));
</script>

<template>
    <Head title="Cart" />

    <UserLayout>
        <div v-if="cart.length === 0" class="container mx-auto px-4 py-24 text-center">
            <div class="h-24 w-24 mx-auto rounded-full bg-primary/10 text-primary grid place-items-center">
                <ShoppingBag class="h-10 w-10" />
            </div>
            <h1 class="mt-6 text-3xl font-bold">Your cart is empty</h1>
            <p class="mt-2 text-muted-foreground">Start exploring fresh, natural goodness.</p>
            <Link
                :href="route('shop')"
                class="mt-6 inline-flex items-center gap-1 rounded-full bg-primary px-7 py-3 text-primary-foreground font-medium shadow-glow hover:bg-primary/90"
            >
                Start shopping <ArrowRight class="h-4 w-4" />
            </Link>
        </div>

        <div v-else class="container mx-auto px-4 py-8">
            <UserBreadcrumb :items="[{ label: 'Home', href: route('home') }, { label: 'Cart' }]" />

            <div class="mt-2 flex items-center justify-between">
                <h1 class="text-3xl md:text-4xl font-bold">Shopping cart</h1>
                <button
                    type="button"
                    class="text-sm text-muted-foreground hover:text-destructive transition-colors"
                    @click="clearCart"
                >
                    Clear all
                </button>
            </div>

            <div class="mt-8 grid lg:grid-cols-[1fr_380px] gap-8">
                <div class="space-y-3">
                    <div
                        v-for="item in cart"
                        :key="item.product.id"
                        class="flex gap-4 p-4 rounded-2xl bg-card border border-border/60 shadow-soft"
                    >
                        <div class="h-24 w-24 sm:h-28 sm:w-28 rounded-2xl overflow-hidden bg-secondary shrink-0">
                            <img :src="item.product.image" :alt="item.product.name" class="h-full w-full object-cover" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <Link :href="route('shop')" class="font-semibold hover:text-primary line-clamp-2">
                                        {{ item.product.name }}
                                    </Link>
                                    <p class="text-xs text-muted-foreground mt-1 font-mono">{{ item.product.code }}</p>
                                </div>
                                <button
                                    type="button"
                                    class="h-8 w-8 grid place-items-center rounded-full hover:bg-destructive/10 hover:text-destructive shrink-0"
                                    @click="removeFromCart(item.product.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center border rounded-full h-9">
                                    <button
                                        type="button"
                                        class="h-9 w-9 grid place-items-center hover:bg-secondary rounded-l-full"
                                        @click="updateQty(item.product.id, item.qty - 1)"
                                    >
                                        <Minus class="h-3.5 w-3.5" />
                                    </button>
                                    <span class="w-9 text-center text-sm font-semibold">{{ item.qty }}</span>
                                    <button
                                        type="button"
                                        class="h-9 w-9 grid place-items-center hover:bg-secondary rounded-r-full"
                                        @click="updateQty(item.product.id, item.qty + 1)"
                                    >
                                        <Plus class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold">${{ (item.product.price * item.qty).toFixed(2) }}</p>
                                    <p v-if="item.discountAmount > 0" class="text-xs text-destructive line-through">
                                        ${{ item.product.oldPrice.toFixed(2) }}<span v-if="item.qty > 1"> each</span>
                                    </p>
                                    <p v-if="item.qty > 1" class="text-xs text-muted-foreground">
                                        ${{ item.product.price.toFixed(2) }} each
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="h-fit rounded-3xl bg-card border border-border/60 shadow-card p-6 lg:sticky lg:top-32">
                    <h3 class="font-bold text-lg">Order summary</h3>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span class="font-medium">${{ cartSubtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Discount</span>
                            <span class="font-medium text-destructive">-${{ cartDiscount.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Delivery</span>
                            <span class="font-medium text-slate-700">${{ deliveryFeePerKg.toFixed(2) }} / 1 KM</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="font-semibold">Total</span>
                            <span class="text-2xl font-bold text-primary">${{ total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <Link
                        :href="route('checkout')"
                        class="mt-6 flex w-full items-center justify-center gap-1 rounded-full bg-primary py-3 text-primary-foreground font-medium shadow-glow hover:bg-primary/90"
                    >
                        Proceed to checkout <ArrowRight class="h-4 w-4" />
                    </Link>

                    <Link :href="route('shop')" class="block text-center mt-3 text-sm text-muted-foreground hover:text-primary">
                        Continue shopping
                    </Link>

                    <div class="mt-5 pt-5 border-t text-xs text-muted-foreground space-y-1.5">
                        <p>🔒 Secure checkout</p>
                    </div>
                </aside>
            </div>
        </div>
    </UserLayout>
</template>
