<template>
    <textarea
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="updateValue"
        :rows="rows"
        :class="[
            'flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-sm',
            'placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
            'disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
            className
        ]">
    </textarea>
</template>

<script>
export default {
    name: "Textarea",

    props: {
        modelValue: {
            type: String,
            default: "",
        },

        placeholder: {
            type: String,
            default: "",
        },

        disabled: {
            type: Boolean,
            default: false,
        },

        rows: {
            type: Number,
            default: 4,
        },

        className: {
            type: String,
            default: "",
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