<template>
    <label
        :for="forId"
        :class="[
            'text-sm font-medium leading-none',
            disabled
                ? 'cursor-not-allowed opacity-70'
                : ''
        ]">

        <slot>
            {{ text }}
        </slot>
    </label>
</template>

<script>
export default {
    name: "Label",

    props: {
        text: {
            type: String,
            default: "",
        },

        forId: {
            type: String,
            default: "",
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },
};
</script>