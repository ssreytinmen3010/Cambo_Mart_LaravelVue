<template>
    <div class="w-full">

        <!-- Tabs List -->
        <div
            class="inline-flex h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground">

            <button
                v-for="(tab, index) in tabs"
                :key="index"
                @click="selectTab(tab.value)"
                :disabled="tab.disabled"
                :class="[
                    'inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium transition-all',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                    tab.disabled
                        ? 'pointer-events-none opacity-50 cursor-not-allowed'
                        : 'cursor-pointer',
                    modelValue === tab.value
                        ? 'bg-background text-foreground shadow'
                        : ''
                ]">

                {{ tab.label }}
            </button>
        </div>

        <!-- Tabs Content -->
        <div
            class="mt-2 ring-offset-background focus-visible:outline-none">

            <div
                v-for="(tab, index) in tabs"
                :key="index"
                v-show="modelValue === tab.value">

                <slot :name="tab.value">
                    {{ tab.content }}
                </slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Tabs",

    props: {
        modelValue: {
            type: [String, Number],
            default: "",
        },

        tabs: {
            type: Array,
            default: () => [],
        },
    },

    emits: ["update:modelValue"],

    methods: {
        selectTab(value) {
            this.$emit(
                "update:modelValue",
                value
            );
        },
    },
};
</script>