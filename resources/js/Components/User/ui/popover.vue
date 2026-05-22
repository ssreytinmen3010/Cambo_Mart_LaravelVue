<template>
    <div class="relative inline-block">

        <!-- Trigger -->
        <div @click="togglePopover">
            <slot name="trigger">
                <button
                    class="rounded-md border px-4 py-2 text-sm hover:bg-accent transition">

                    Open Popover
                </button>
            </slot>
        </div>

        <!-- Popover Content -->
        <transition name="popover">

            <div
                v-if="open"
                :class="[
                    'absolute z-50 w-72 rounded-md border bg-popover p-4 text-popover-foreground shadow-md outline-none',
                    positionClass
                ]">

                <slot />
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: "Popover",

    props: {
        align: {
            type: String,
            default: "center",
        },

        side: {
            type: String,
            default: "bottom",
        },

        sideOffset: {
            type: Number,
            default: 4,
        },
    },

    data() {
        return {
            open: false,
        };
    },

    computed: {
        positionClass() {

            switch (this.side) {

                case "top":
                    return "bottom-full mb-2";

                case "left":
                    return "right-full mr-2";

                case "right":
                    return "left-full ml-2";

                default:
                    return "top-full mt-2";
            }
        },
    },

    methods: {
        togglePopover() {
            this.open = !this.open;
        },
    },
};
</script>

<style scoped>
.popover-enter-active,
.popover-leave-active {
    transition: all 0.2s ease;
}

.popover-enter-from,
.popover-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>