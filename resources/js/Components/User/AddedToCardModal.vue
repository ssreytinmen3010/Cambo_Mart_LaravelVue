<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const lastAdded = ref({
    name: 'Fresh Organic Apple',
    code: 'PRD-001',
    price: 12.99,
    image: 'https://via.placeholder.com/150',
});

const cartCount = ref(3);
const cartTotal = ref(42.5);

function closeLastAdded() {
    lastAdded.value = null;
}
</script>

<template>
    <transition name="fade">
        <div v-if="lastAdded" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl animate-float-up">
                <div class="p-6">
                    <div class="flex items-center gap-2 text-primary font-semibold">
                        ✅
                        <span>Added to your cart</span>
                    </div>

                    <div class="mt-5 flex gap-4">
                        <div class="h-24 w-24 rounded-2xl overflow-hidden bg-secondary shrink-0">
                            <img :src="lastAdded.image" :alt="lastAdded.name" class="h-full w-full object-cover" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-semibold leading-snug line-clamp-2">{{ lastAdded.name }}</p>
                            <p class="text-xs text-muted-foreground mt-1 font-mono">{{ lastAdded.code }}</p>
                            <p class="mt-2 text-lg font-bold text-primary">${{ Number(lastAdded.price).toFixed(2) }}</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-secondary/60 p-4 flex items-center justify-between text-sm">
                        <span class="text-muted-foreground">🛒 {{ cartCount }} items in cart</span>
                        <span class="font-bold">${{ Number(cartTotal).toFixed(2) }}</span>
                    </div>

                    <div class="mt-5 flex gap-2">
                        <button
                            type="button"
                            class="flex-1 rounded-full border px-4 py-3 hover:bg-secondary transition"
                            @click="closeLastAdded"
                        >
                            Continue shopping
                        </button>

                        <Link :href="route('cart')" class="flex-1" @click="closeLastAdded">
                            <span class="block w-full rounded-full bg-primary text-primary-foreground px-4 py-3 text-center hover:bg-primary/90 transition">
                                View cart
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
