<template>
    <div
        class="flex aspect-video justify-center text-xs">

        <!-- Chart -->
        <div class="w-full h-full">
            <slot />
        </div>

        <!-- Legend -->
        <div
            v-if="legend.length"
            class="flex items-center justify-center gap-4 pt-3">

            <div
                v-for="(item, index) in legend"
                :key="index"
                class="flex items-center gap-1.5">

                <div
                    class="h-2 w-2 rounded-[2px]"
                    :style="{ backgroundColor: item.color }">
                </div>

                <span>
                    {{ item.label }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ChartContainer",

    props: {
        config: {
            type: Object,
            default: () => ({}),
        },

        legend: {
            type: Array,
            default: () => [],
        },
    },
};
</script>