<script setup>
import { Star } from 'lucide-vue-next';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
});

const activeCats = defineModel('activeCats', { default: () => [] });
const activeBrands = defineModel('activeBrands', { default: () => [] });
const inStockOnly = defineModel('inStockOnly', { default: false });
const onSaleOnly = defineModel('onSaleOnly', { default: false });
const minRating = defineModel('minRating', { default: 0 });
const maxPrice = defineModel('maxPrice', { default: 60 });

const emit = defineEmits(['reset']);

function toggleInArray(list, id) {
    const index = list.indexOf(id);
    if (index > -1) {
        list.splice(index, 1);
    } else {
        list.push(id);
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="bg-card rounded-2xl p-4 border border-border/60">
            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Category</p>
            <div class="space-y-2">
                <label v-for="c in categories" :key="c.id" class="flex items-center gap-2 cursor-pointer text-xs">
                    <input
                        type="checkbox"
                        :checked="activeCats.includes(c.id)"
                        class="sr-only"
                        @change="toggleInArray(activeCats, c.id)"
                    />
                    <span
                        class="grid h-4 w-4 shrink-0 place-items-center rounded-[5px] border transition"
                        :class="activeCats.includes(c.id) ? 'border-primary bg-primary text-white' : 'border-black/60 bg-white text-primary'"
                    >
                        <svg v-if="activeCats.includes(c.id)" class="h-2.5 w-2.5" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="font-medium leading-none">{{ c.name }}</span>
                    <span class="ml-auto text-[10px] text-muted-foreground">{{ c.count }}</span>
                </label>
            </div>
        </div>

        <div class="bg-card rounded-2xl p-4 border border-border/60">
            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Brand</p>
            <div class="space-y-2">
                <label v-for="b in brands" :key="b.id" class="flex items-center gap-2 cursor-pointer text-xs">
                    <input
                        type="checkbox"
                        :checked="activeBrands.includes(b.id)"
                        class="sr-only"
                        @change="toggleInArray(activeBrands, b.id)"
                    />
                    <span
                        class="grid h-4 w-4 shrink-0 place-items-center rounded-[5px] border transition"
                        :class="activeBrands.includes(b.id) ? 'border-primary bg-primary text-white' : 'border-black/60 bg-white text-primary'"
                    >
                        <svg v-if="activeBrands.includes(b.id)" class="h-2.5 w-2.5" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border/60 bg-muted">
                        <img v-if="b.image" :src="b.image" :alt="b.name" class="h-full w-full object-cover" />
                        <span v-else class="text-[9px] font-bold text-muted-foreground">{{ b.name.charAt(0) }}</span>
                    </span>
                    <span class="font-medium leading-none">{{ b.name }}</span>
                </label>
            </div>
        </div>

        <div class="bg-card rounded-2xl p-4 border border-border/60 space-y-2">
            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Promotions</p>
            <label class="flex items-center gap-2 text-xs cursor-pointer">
                <input v-model="onSaleOnly" type="checkbox" class="sr-only" />
                <span
                    class="grid h-4 w-4 shrink-0 place-items-center rounded-[5px] border transition"
                    :class="onSaleOnly ? 'border-primary bg-primary text-white' : 'border-black/60 bg-white text-primary'"
                >
                    <svg v-if="onSaleOnly" class="h-2.5 w-2.5" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="font-medium leading-none">On sale</span>
            </label>
            <label class="flex items-center gap-2 text-xs cursor-pointer">
                <input v-model="inStockOnly" type="checkbox" class="sr-only" />
                <span
                    class="grid h-4 w-4 shrink-0 place-items-center rounded-[5px] border transition"
                    :class="inStockOnly ? 'border-primary bg-primary text-white' : 'border-black/60 bg-white text-primary'"
                >
                    <svg v-if="inStockOnly" class="h-2.5 w-2.5" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="font-medium leading-none">In stock only</span>
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
    </div>
</template>
