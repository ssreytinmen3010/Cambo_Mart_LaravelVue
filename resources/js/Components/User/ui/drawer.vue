<template>
    <transition name="drawer-fade">

        <!-- Overlay -->
        <div
            v-if="open"
            class="fixed inset-0 z-50 bg-black/80"
            @click="closeDrawer">

            <!-- Drawer -->
            <div
                @click.stop
                class="fixed inset-x-0 bottom-0 z-50 mt-24 flex h-auto flex-col rounded-t-[10px] border bg-background">

                <!-- Handle -->
                <div
                    class="mx-auto mt-4 h-2 w-[100px] rounded-full bg-muted">
                </div>

                <!-- Header -->
                <div
                    v-if="title || description"
                    class="grid gap-1.5 p-4 text-center sm:text-left">

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

                <!-- Content -->
                <div class="p-4">
                    <slot />
                </div>

                <!-- Footer -->
                <div
                    v-if="$slots.footer"
                    class="mt-auto flex flex-col gap-2 p-4">

                    <slot name="footer" />
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "Drawer",

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

        shouldScaleBackground: {
            type: Boolean,
            default: true,
        },
    },

    emits: ["update:modelValue"],

    computed: {
        open() {
            return this.modelValue;
        },
    },

    methods: {
        closeDrawer() {
            this.$emit("update:modelValue", false);
        },
    },
};
</script>

<style scoped>
.drawer-fade-enter-active,
.drawer-fade-leave-active {
    transition: opacity 0.2s ease;
}

.drawer-fade-enter-from,
.drawer-fade-leave-to {
    opacity: 0;
}
</style>