<template>
    <div
        :class="[
            'flex items-center justify-center gap-1',
            className
        ]">

        <!-- Toggle Items -->
        <button
            v-for="(item, index) in items"
            :key="index"
            @click="toggleItem(item.value)"
            :disabled="item.disabled"
            :class="[
                toggleClass(item.value),
                item.disabled
                    ? 'pointer-events-none opacity-50 cursor-not-allowed'
                    : 'cursor-pointer'
            ]">

            {{ item.label }}
        </button>
    </div>
</template>

<script>
export default {
    name: "ToggleGroup",

    props: {
        modelValue: {
            type: [Array, String],
            default: () => [],
        },

        items: {
            type: Array,
            default: () => [],
        },

        multiple: {
            type: Boolean,
            default: false,
        },

        variant: {
            type: String,
            default: "default",
        },

        size: {
            type: String,
            default: "default",
        },

        className: {
            type: String,
            default: "",
        },
    },

    emits: ["update:modelValue"],

    methods: {
        toggleItem(value) {

            if (this.multiple) {

                let values = [...this.modelValue];

                if (values.includes(value)) {

                    values =
                        values.filter(v => v !== value);

                } else {
                    values.push(value);
                }

                this.$emit(
                    "update:modelValue",
                    values
                );

            } else {

                this.$emit(
                    "update:modelValue",
                    this.modelValue === value
                        ? ""
                        : value
                );
            }
        },

        isActive(value) {

            if (this.multiple) {
                return this.modelValue.includes(value);
            }

            return this.modelValue === value;
        },

        toggleClass(value) {

            const active =
                this.isActive(value);

            const base =
                "inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors";

            const sizeClass = {
                default: "h-9 px-3",
                sm: "h-8 px-2 text-xs",
                lg: "h-10 px-5",
            };

            const variantClass = active
                ? "bg-primary text-primary-foreground shadow"
                : "bg-transparent hover:bg-muted";

            return `
                ${base}
                ${sizeClass[this.size]}
                ${variantClass}
            `;
        },
    },
};
</script>