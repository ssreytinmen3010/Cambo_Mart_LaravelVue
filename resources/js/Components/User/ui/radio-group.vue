<template>
    <div class="grid gap-2">

        <!-- Radio Items -->
        <label
            v-for="(option, index) in options"
            :key="index"
            class="flex items-center gap-2 cursor-pointer">

            <!-- Radio -->
            <div
                @click="selectOption(option.value)"
                :class="[
                    'flex h-4 w-4 items-center justify-center rounded-full border border-primary shadow transition',
                    modelValue === option.value
                        ? 'bg-primary text-primary-foreground'
                        : ''
                ]">

                <!-- Indicator -->
                <div
                    v-if="modelValue === option.value"
                    class="h-2 w-2 rounded-full bg-white">
                </div>
            </div>

            <!-- Label -->
            <span class="text-sm">
                {{ option.label }}
            </span>
        </label>
    </div>
</template>

<script>
export default {
    name: "RadioGroup",

    props: {
        modelValue: {
            type: [String, Number],
            default: "",
        },

        options: {
            type: Array,
            default: () => [],
        },
    },

    emits: ["update:modelValue"],

    methods: {
        selectOption(value) {
            this.$emit(
                "update:modelValue",
                value
            );
        },
    },
};
</script>