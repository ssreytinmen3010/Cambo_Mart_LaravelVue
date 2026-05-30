<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

const open = ref(false);
const page = usePage();

const promotionSeason = computed(() => page.props.promotionSeason ?? null);

const promoImage = computed(() => 
    promotionSeason.value?.image || 
    'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=900&q=80'
);

onMounted(() => {
    if (typeof window === 'undefined' || !promotionSeason.value) return;
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
        console.error('Failed to set promo state in storage:', e);
    }
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-350 ease-out"
        enter-from-class="opacity-0 scale-95 translate-y-4"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 translate-y-4"
    >
        <div
            v-if="open && promotionSeason"
            class="fixed inset-0 z-[120] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-label="Promotion"
        >
            <div 
                class="absolute inset-0 bg-slate-950/75" 
                aria-hidden="true" 
                @click="close"
            />

            <article class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-slate-900 shadow-2xl ring-1 ring-white/10">
                
                <div class="relative aspect-[16/10] w-full">
                    <img
                        :src="promoImage"
                        alt="Seasonal promotion image"
                        class="absolute inset-0 h-full w-full object-cover"
                    />
                    
                    <div
                        v-if="promotionSeason.code"
                        class="absolute left-4 top-4 rounded-md bg-black/50 px-2.5 py-1"
                    >
                        <span class="font-mono text-xs font-bold tracking-wider text-white">
                            {{ promotionSeason.code }}
                        </span>
                    </div>

                    <button
                        type="button"
                        class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-black/50 text-white transition-all hover:bg-black/70 hover:scale-105 active:scale-95"
                        aria-label="Close promotion"
                        @click="close"
                    >
                        <X class="h-4 w-4" stroke-width="2.5" />
                    </button>
                </div>

                <div class="border-t border-white/5 bg-slate-950 p-4">
                    <button
                        type="button"
                        class="w-full rounded-xl bg-white py-3 text-sm font-semibold text-slate-900 transition-all hover:bg-slate-100 active:scale-[0.98]"
                        @click="close"
                    >
                        Thanks, later
                    </button>
                </div>
            </article>
        </div>
    </Transition>
</template>