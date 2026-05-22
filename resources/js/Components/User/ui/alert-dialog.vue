<template>
    <transition name="fade">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80">

            <!-- Dialog -->
            <div
                class="fixed left-1/2 top-1/2 z-50 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg duration-200 sm:rounded-lg">

                <!-- Header -->
                <div class="flex flex-col space-y-2 text-center sm:text-left">

                    <h2 class="text-lg font-semibold">
                        {{ title }}
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>

                <!-- Footer -->
                <div
                    class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">

                    <!-- Cancel -->
                    <button
                        @click="cancel"
                        class="mt-2 sm:mt-0 inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium hover:bg-secondary transition">

                        Cancel
                    </button>

                    <!-- Confirm -->
                    <button
                        @click="confirm"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90 transition">

                        Continue
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "AlertDialog",

    props: {
        open: {
            type: Boolean,
            default: false,
        },

        title: {
            type: String,
            default: "Are you absolutely sure?",
        },

        description: {
            type: String,
            default:
                "This action cannot be undone. This will permanently remove your data.",
        },
    },

    emits: ["update:open", "confirm", "cancel"],

    methods: {
        confirm() {
            this.$emit("confirm");
            this.$emit("update:open", false);
        },

        cancel() {
            this.$emit("cancel");
            this.$emit("update:open", false);
        },
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>