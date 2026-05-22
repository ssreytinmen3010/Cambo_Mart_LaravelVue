<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :disabled="disabled"
        @click="toggleSwitch"
        :class="[
            'peer inline-flex h-5 w-9 shrink-0 items-center rounded-full border-2 border-transparent shadow-sm transition-colors',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
            disabled
                ? 'cursor-not-allowed opacity-50'
                : 'cursor-pointer',
            modelValue
                ? 'bg-primary'
                : 'bg-input'
        ]">

        <!-- Thumb -->
        <span
            :class="[
                'pointer-events-none block h-4 w-4 rounded-full bg-background shadow-lg transition-transform',
                modelValue
                    ? 'translate-x-4'
                    : 'translate-x-0'
            ]">
        </span>
    </button>
</template>

<script>
export default {
    name: "Switch",

    props: {
        modelValue: {
            type: Boolean,
            default: false,
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["update:modelValue"],

    methods: {
        toggleSwitch() {

            if (this.disabled) return;

            this.$emit(
                "update:modelValue",
                !this.modelValue
            );
        },
    },
};
</script>