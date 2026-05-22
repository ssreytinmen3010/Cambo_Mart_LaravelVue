<template>
    <button
        type="button"
        @click="toggle"
        :disabled="disabled"
        :class="[
            toggleClasses,
            className
        ]">

        <slot>
            {{ label }}
        </slot>
    </button>
</template>

<script>
export default {
    name: "Toggle",

    props: {
        modelValue: {
            type: Boolean,
            default: false,
        },

        label: {
            type: String,
            default: "",
        },

        variant: {
            type: String,
            default: "default",
        },

        size: {
            type: String,
            default: "default",
        },

        disabled: {
            type: Boolean,
            default: false,
        },

        className: {
            type: String,
            default: "",
        },
    },

    emits: ["update:modelValue"],

    computed: {
        toggleClasses() {

            const base =
                "inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors";

            const state =
                this.modelValue
                    ? "bg-accent text-accent-foreground"
                    : "bg-transparent hover:bg-muted hover:text-muted-foreground";

            const variantClass = {
                default: "",
                outline:
                    "border border-input shadow-sm hover:bg-accent hover:text-accent-foreground",
            };

            const sizeClass = {
                default: "h-9 px-2 min-w-9",
                sm: "h-8 px-1.5 min-w-8 text-xs",
                lg: "h-10 px-2.5 min-w-10",
            };

            const disabledClass =
                this.disabled
                    ? "pointer-events-none opacity-50 cursor-not-allowed"
                    : "cursor-pointer";

            return `
                ${base}
                ${state}
                ${variantClass[this.variant]}
                ${sizeClass[this.size]}
                ${disabledClass}
            `;
        },
    },

    methods: {
        toggle() {

            if (this.disabled) return;

            this.$emit(
                "update:modelValue",
                !this.modelValue
            );
        },
    },
};
</script>