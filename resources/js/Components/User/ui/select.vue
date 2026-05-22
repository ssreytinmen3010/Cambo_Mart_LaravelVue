<template>
    <div class="relative w-full">

        <!-- Trigger -->
        <button
            @click="toggleSelect"
            :disabled="disabled"
            class="flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background cursor-pointer focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50">

            <span
                class="truncate"
                :class="!selectedLabel ? 'text-muted-foreground' : ''">

                {{ selectedLabel || placeholder }}
            </span>

            <!-- Chevron -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="h-4 w-4 opacity-50">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m19 9-7 7-7-7" />
            </svg>
        </button>

        <!-- Content -->
        <transition name="select">

            <div
                v-if="open"
                class="absolute z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-md border bg-popover text-popover-foreground shadow-md">

                <!-- Label -->
                <div
                    v-if="label"
                    class="px-2 py-1.5 text-sm font-semibold">

                    {{ label }}
                </div>

                <!-- Items -->
                <div class="p-1">

                    <div
                        v-for="(item, index) in options"
                        :key="index">

                        <!-- Separator -->
                        <div
                            v-if="item.separator"
                            class="-mx-1 my-1 h-px bg-muted">
                        </div>

                        <!-- Item -->
                        <button
                            v-else
                            @click="selectItem(item)"
                            :disabled="item.disabled"
                            :class="[
                                'relative flex w-full items-center rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none transition',
                                item.disabled
                                    ? 'pointer-events-none opacity-50'
                                    : 'hover:bg-accent hover:text-accent-foreground'
                            ]">

                            <!-- Text -->
                            <span>
                                {{ item.label }}
                            </span>

                            <!-- Check -->
                            <span
                                v-if="modelValue === item.value"
                                class="absolute right-2 flex h-3.5 w-3.5 items-center justify-center">

                                ✔
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: "Select",

    props: {
        modelValue: {
            type: [String, Number],
            default: "",
        },

        options: {
            type: Array,
            default: () => [],
        },

        placeholder: {
            type: String,
            default: "Select option",
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

    data() {
        return {
            open: false,
        };
    },

    computed: {
        selectedLabel() {

            const selected =
                this.options.find(
                    item => item.value === this.modelValue
                );

            return selected
                ? selected.label
                : "";
        },
    },

    methods: {
        toggleSelect() {
            if (this.disabled) return;

            this.open = !this.open;
        },

        selectItem(item) {

            if (item.disabled) return;

            this.$emit(
                "update:modelValue",
                item.value
            );

            this.open = false;
        },
    },
};
</script>

<style scoped>
.select-enter-active,
.select-leave-active {
    transition: all 0.15s ease;
}

.select-enter-from,
.select-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>