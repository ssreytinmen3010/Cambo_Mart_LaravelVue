<template>
    <transition name="dialog-fade">

        <!-- Overlay -->
        <div
            v-if="open"
            class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center"
            @click.self="closeDialog">

            <!-- Content -->
            <div
                class="relative z-50 grid w-full max-w-lg gap-4 border bg-background p-6 shadow-lg duration-200 sm:rounded-lg">

                <!-- Close Button -->
                <button
                    @click="closeDialog"
                    class="absolute right-4 top-4 rounded-sm opacity-70 transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">

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
                    class="flex flex-col space-y-1.5 text-center sm:text-left">

                    <!-- Title -->
                    <h2
                        v-if="title"
                        class="text-lg font-semibold leading-none tracking-tight">

                        {{ title }}
                    </h2>

                    <!-- Description -->
                    <p
                        v-if="description"
                        class="text-sm text-muted-foreground">

                        {{ description }}
                    </p>
                </div>

                <!-- Body -->
                <div>
                    <slot />
                </div>

                <!-- Footer -->
                <div
                    v-if="$slots.footer"
                    class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">

                    <slot name="footer" />
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "Dialog",

    props: {
        modelValue: {
            type: Boolean,
            default: false,
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
    },

    methods: {
        closeDialog() {
            this.$emit("update:modelValue", false);
        },
    },
};
</script>

<style scoped>
.dialog-fade-enter-active,
.dialog-fade-leave-active {
    transition: opacity 0.2s ease;
}

.dialog-fade-enter-from,
.dialog-fade-leave-to {
    opacity: 0;
}
</style>