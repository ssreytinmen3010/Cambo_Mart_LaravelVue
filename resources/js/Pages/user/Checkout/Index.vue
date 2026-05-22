<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CreditCard, Truck, Wallet, CheckCircle2, ShieldCheck } from 'lucide-vue-next';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';
import { useStore } from '@/composables/useStore';

const { cart, cartTotal, clearCart } = useStore();

const method = ref('card');
const placing = ref(false);

const methods = [
    { id: 'card', name: 'Credit / Debit card', desc: 'Visa, Mastercard, JCB', icon: CreditCard },
    { id: 'aba', name: 'ABA Pay', desc: 'Scan with the ABA mobile app', icon: Wallet },
    { id: 'cod', name: 'Cash on delivery', desc: 'Pay when you receive your order', icon: Truck },
];

const shipping = computed(() => (cartTotal.value > 25 || cartTotal.value === 0 ? 0 : 3.5));
const tax = computed(() => cartTotal.value * 0.05);
const total = computed(() => cartTotal.value + shipping.value + tax.value);

function placeOrder() {
    placing.value = true;
    setTimeout(() => {
        clearCart();
        placing.value = false;
        router.visit(route('user.profile'), {
            onSuccess: () => {},
        });
    }, 900);
}
</script>

<template>
    <Head title="Checkout" />

    <UserLayout>
        <div v-if="cart.length === 0" class="container mx-auto px-4 py-24 text-center">
            <h1 class="text-2xl font-bold">Your cart is empty</h1>
            <Link
                :href="route('shop')"
                class="mt-5 inline-block rounded-full bg-primary px-7 py-3 text-primary-foreground font-medium hover:bg-primary/90"
            >
                Go shopping
            </Link>
        </div>

        <div v-else class="container mx-auto px-4 py-8">
            <UserBreadcrumb
                :items="[
                    { label: 'Home', href: route('home') },
                    { label: 'Cart', href: route('cart') },
                    { label: 'Checkout' },
                ]"
            />

            <h1 class="mt-2 text-3xl md:text-4xl font-bold">Checkout</h1>

            <div class="mt-6 flex items-center gap-3 text-xs flex-wrap">
                <div v-for="(step, i) in ['Cart', 'Details', 'Payment']" :key="step" class="flex items-center gap-2">
                    <span
                        class="h-7 w-7 rounded-full grid place-items-center font-bold"
                        :class="i <= 1 ? 'bg-primary text-primary-foreground' : 'bg-secondary text-muted-foreground'"
                    >
                        {{ i + 1 }}
                    </span>
                    <span :class="i <= 1 ? 'font-semibold' : 'text-muted-foreground'">{{ step }}</span>
                    <span v-if="i < 2" class="w-8 h-px bg-border mx-2 hidden sm:block" />
                </div>
            </div>

            <form class="mt-8 grid lg:grid-cols-[1fr_380px] gap-8" @submit.prevent="placeOrder">
                <div class="space-y-6">
                    <section class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                        <h3 class="font-bold mb-4">Contact & delivery</h3>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Full name</span>
                                <input required class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="John Sok" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Phone</span>
                                <input required type="tel" class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="+855 12 345 678" />
                            </label>
                            <label class="block sm:col-span-2">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Email</span>
                                <input required type="email" class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="you@email.com" />
                            </label>
                            <label class="block sm:col-span-2">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Delivery address</span>
                                <input required class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="Street, building, apartment" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">City</span>
                                <input required class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="Phnom Penh" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Postal code</span>
                                <input class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="120000" />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                        <h3 class="font-bold mb-4">Payment method</h3>
                        <div class="grid sm:grid-cols-3 gap-3">
                            <label
                                v-for="m in methods"
                                :key="m.id"
                                class="relative cursor-pointer rounded-2xl border p-4 block transition-all"
                                :class="method === m.id ? 'border-primary bg-primary/5 shadow-soft' : 'border-border hover:border-primary/40'"
                            >
                                <input v-model="method" type="radio" name="paymentMethod" :value="m.id" class="sr-only" />
                                <component :is="m.icon" class="h-6 w-6 text-primary" />
                                <p class="mt-3 font-semibold text-sm">{{ m.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ m.desc }}</p>
                                <CheckCircle2 v-if="method === m.id" class="absolute top-3 right-3 h-5 w-5 text-primary" />
                            </label>
                        </div>

                        <div v-if="method === 'card'" class="mt-4 grid sm:grid-cols-2 gap-3">
                            <label class="block sm:col-span-2">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Card number</span>
                                <input class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="1234 5678 9012 3456" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Expiry</span>
                                <input class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="MM/YY" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium text-muted-foreground mb-1.5 block">CVC</span>
                                <input class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="123" />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-3xl bg-card border border-border/60 shadow-soft p-6">
                        <h3 class="font-bold mb-4">Order notes (optional)</h3>
                        <textarea
                            rows="3"
                            placeholder="Delivery instructions, gift notes…"
                            class="w-full rounded-2xl border bg-background p-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 resize-none"
                        />
                    </section>
                </div>

                <aside class="h-fit rounded-3xl bg-card border border-border/60 shadow-card p-6 lg:sticky lg:top-32">
                    <h3 class="font-bold">Your order</h3>
                    <div class="mt-4 space-y-3 max-h-72 overflow-auto pr-1">
                        <div v-for="{ product, qty } in cart" :key="product.id" class="flex gap-3 items-center text-sm">
                            <div class="relative h-14 w-14 rounded-xl overflow-hidden bg-secondary shrink-0">
                                <img :src="product.image" alt="" class="h-full w-full object-cover" />
                                <span class="absolute -top-1 -right-1 h-5 w-5 grid place-items-center rounded-full bg-foreground text-background text-[10px] font-bold">
                                    {{ qty }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium line-clamp-1">{{ product.name }}</p>
                                <p class="text-xs text-muted-foreground">${{ product.price.toFixed(2) }}</p>
                            </div>
                            <p class="font-semibold">${{ (product.price * qty).toFixed(2) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>${{ cartTotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Shipping</span>
                            <span>{{ shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}` }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Tax</span>
                            <span>${{ tax.toFixed(2) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">${{ total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full mt-5 rounded-full bg-primary py-3 text-primary-foreground font-medium shadow-glow hover:bg-primary/90 disabled:opacity-60"
                        :disabled="placing"
                    >
                        {{ placing ? 'Placing order…' : `Place order · $${total.toFixed(2)}` }}
                    </button>

                    <p class="mt-3 text-xs text-muted-foreground flex items-center gap-1.5">
                        <ShieldCheck class="h-3.5 w-3.5 text-primary" />
                        Encrypted, secure checkout
                    </p>
                </aside>
            </form>
        </div>
    </UserLayout>
</template>
