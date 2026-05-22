<template>
    <div
        class="relative w-full overflow-hidden"
        :style="{ paddingBottom: `${100 / ratio}%` }">

        <div class="absolute inset-0">
            <slot />
        </div>
    </div>
</template>

<script>
export default {
    name: "AspectRatio",

    props: {
        ratio: {
            type: Number,
            default: 1,
        },
    },
};
</script>