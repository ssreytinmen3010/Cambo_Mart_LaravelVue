<template>
    <input
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="updateValue"
        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" />
</template>

<script>
export default {
    name: "Input",

    props: {
        modelValue: {
            type: [String, Number],
            default: "",
        },

        type: {
            type: String,
            default: "text",
        },

        placeholder: {
            type: String,
            default: "",
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["update:modelValue"],

    methods: {
        updateValue(event) {
            this.$emit(
                "update:modelValue",
                event.target.value
            );
        },
    },
};
</script>