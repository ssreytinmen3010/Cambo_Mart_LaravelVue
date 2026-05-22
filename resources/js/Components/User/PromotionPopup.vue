<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const open = ref(false);

onMounted(() => {
    if (typeof window === 'undefined') return;
    if (sessionStorage.getItem('cm_promo_seen')) return;

    setTimeout(() => {
        open.value = true;
    }, 1500);
});

function close() {
    open.value = false;

    try {
        sessionStorage.setItem('cm_promo_seen', '1');
    } catch (e) {
        console.log(e);
    }
}
</script>

<template>
    <transition name="fade">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl border-0">
                <div class="grid md:grid-cols-5">
                    <div class="md:col-span-2 bg-gradient-brand p-6 grid place-items-center">
                        <img
                            src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=500&q=80"
                            alt="Promo"
                            class="rounded-2xl shadow-glow"
                        />
                    </div>

                    <div class="md:col-span-3 p-7">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-primary">
                            ✨ New here?
                        </span>

                        <h2 class="mt-3 text-2xl font-bold leading-tight">
                            Get <span class="text-gradient-brand">15% off</span> your first order
                        </h2>

                        <p class="mt-2 text-sm text-muted-foreground">
                            Join CamboMart today and enjoy a welcome discount, plus free delivery on your first basket.
                        </p>

                        <div class="mt-5 space-y-2">
                            <Link :href="route('register')" class="block" @click="close">
                                <span class="block w-full rounded-full bg-primary text-primary-foreground py-3 font-medium text-center hover:bg-primary/90 transition">
                                    Claim my 15% off
                                </span>
                            </Link>

                            <Link :href="route('shop')" class="block w-full text-xs text-muted-foreground hover:text-foreground text-center" @click="close">
                                No thanks, take me to the shop
                            </Link>
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
