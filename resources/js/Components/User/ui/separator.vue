<template>
    <div
        role="separator"
        :aria-orientation="orientation"
        :class="[
            'shrink-0 bg-border',
            orientation === 'horizontal'
                ? 'h-[1px] w-full'
                : 'h-full w-[1px]',
            className
        ]">
    </div>
</template>

<script>
export default {
    name: "Separator",

    props: {
        orientation: {
            type: String,
            default: "horizontal",
        },

        decorative: {
            type: Boolean,
            default: true,
        },

        className: {
            type: String,
            default: "",
        },
    },
};
</script>