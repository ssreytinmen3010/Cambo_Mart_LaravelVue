<template>
    <label
        class="inline-flex items-center gap-2 cursor-pointer">

        <!-- Checkbox -->
        <div
            @click="toggle"
            :class="[
                'grid place-content-center h-4 w-4 shrink-0 rounded-sm border shadow transition cursor-pointer',
                checked
                    ? 'bg-primary text-primary-foreground border-primary'
                    : 'border-primary bg-white'
            ]">

            <svg
                v-if="checked"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="3"
                stroke="currentColor"
                class="h-3 w-3">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m5 13 4 4L19 7" />
            </svg>
        </div>

        <!-- Label -->
        <span v-if="label">
            {{ label }}
        </span>
    </label>
</template>

<script>
export default {
    name: "Checkbox",

    props: {
        modelValue: {
            type: Boolean,
            default: false,
        },

        label: {
            type: String,
            default: "",
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["update:modelValue"],

    computed: {
        checked() {
            return this.modelValue;
        },
    },

    methods: {
        toggle() {
            if (this.disabled) return;

            this.$emit(
                "update:modelValue",
                !this.checked
            );
        },
    },
};
</script>