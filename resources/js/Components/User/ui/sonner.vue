<template>
    <div
        class="fixed top-4 right-4 z-[9999] flex flex-col gap-3">

        <!-- Toast -->
        <transition-group
            name="toast"
            tag="div">

            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="group rounded-md border border-border bg-background p-4 text-foreground shadow-lg min-w-[250px]">

                <!-- Title -->
                <div class="font-medium">
                    {{ toast.title }}
                </div>

                <!-- Description -->
                <div
                    v-if="toast.description"
                    class="mt-1 text-sm text-muted-foreground">

                    {{ toast.description }}
                </div>

                <!-- Actions -->
                <div
                    v-if="toast.action || toast.cancel"
                    class="mt-3 flex gap-2">

                    <!-- Action -->
                    <button
                        v-if="toast.action"
                        @click="toast.action.onClick"
                        class="rounded-md bg-primary px-3 py-1 text-sm text-primary-foreground">

                        {{ toast.action.label }}
                    </button>

                    <!-- Cancel -->
                    <button
                        v-if="toast.cancel"
                        @click="removeToast(toast.id)"
                        class="rounded-md bg-muted px-3 py-1 text-sm text-muted-foreground">

                        {{ toast.cancel.label || 'Cancel' }}
                    </button>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<script>
export default {
    name: "Toaster",

    data() {
        return {
            toasts: [],
        };
    },

    methods: {
        showToast(toast) {

            const id = Date.now();

            this.toasts.push({
                id,
                ...toast,
            });

            setTimeout(() => {
                this.removeToast(id);
            }, toast.duration || 3000);
        },

        removeToast(id) {
            this.toasts =
                this.toasts.filter(
                    toast => toast.id !== id
                );
        },
    },
};
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.2s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>