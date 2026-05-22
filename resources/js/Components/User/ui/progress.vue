<template>
    <div
        class="relative h-2 w-full overflow-hidden rounded-full bg-primary/20">

        <!-- Progress Indicator -->
        <div
            class="h-full bg-primary transition-all duration-300"
            :style="{
                width: `${safeValue}%`
            }">
        </div>
    </div>
</template>

<script>
export default {
    name: "Progress",

    props: {
        value: {
            type: Number,
            default: 0,
        },
    },

    computed: {
        safeValue() {
            return Math.min(
                100,
                Math.max(0, this.value)
            );
        },
    },
};
</script>