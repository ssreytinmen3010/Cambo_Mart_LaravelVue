<template>
    <transition name="sheet-fade">

        <!-- Overlay -->
        <div
            v-if="open"
            class="fixed inset-0 z-50 bg-black/80"
            @click="closeSheet">

            <!-- Sheet -->
            <div
                @click.stop
                :class="sheetClass">

                <!-- Close Button -->
                <button
                    @click="closeSheet"
                    class="absolute right-4 top-4 rounded-sm opacity-70 transition-opacity hover:opacity-100 focus:outline-none">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-4 w-4">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18 18 6M6 6l12 12" />
                    </svg>

                    <span class="sr-only">
                        Close
                    </span>
                </button>

                <!-- Header -->
                <div
                    v-if="title || description"
                    class="flex flex-col space-y-2 text-center sm:text-left">

                    <!-- Title -->
                    <h2
                        v-if="title"
                        class="text-lg font-semibold text-foreground">

                        {{ title }}
                    </h2>

                    <!-- Description -->
                    <p
                        v-if="description"
                        class="text-sm text-muted-foreground">

                        {{ description }}
                    </p>
                </div>

                <!-- Content -->
                <div class="mt-4">
                    <slot />
                </div>

                <!-- Footer -->
                <div
                    v-if="$slots.footer"
                    class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">

                    <slot name="footer" />
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "Sheet",

    props: {
        modelValue: {
            type: Boolean,
            default: false,
        },

        side: {
            type: String,
            default: "right",
        },

        title: {
            type: String,
            default: "",
        },

        description: {
            type: String,
            default: "",
        },
    },

    emits: ["update:modelValue"],

    computed: {
        open() {
            return this.modelValue;
        },

        sheetClass() {

            const base =
                "fixed z-50 bg-background p-6 shadow-lg transition ease-in-out";

            switch (this.side) {

                case "top":
                    return `${base} inset-x-0 top-0 border-b`;

                case "bottom":
                    return `${base} inset-x-0 bottom-0 border-t`;

                case "left":
                    return `${base} inset-y-0 left-0 h-full w-3/4 border-r sm:max-w-sm`;

                default:
                    return `${base} inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm`;
            }
        },
    },

    methods: {
        closeSheet() {
            this.$emit(
                "update:modelValue",
                false
            );
        },
    },
};
</script>

<style scoped>
.sheet-fade-enter-active,
.sheet-fade-leave-active {
    transition: opacity 0.2s ease;
}

.sheet-fade-enter-from,
.sheet-fade-leave-to {
    opacity: 0;
}
</style>