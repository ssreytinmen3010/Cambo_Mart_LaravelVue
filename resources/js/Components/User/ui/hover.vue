<template>
    <div
        class="relative inline-block"
        @mouseenter="openCard"
        @mouseleave="closeCard">

        <!-- Trigger -->
        <div>
            <slot name="trigger" />
        </div>

        <!-- Hover Card -->
        <transition name="hover-card">

            <div
                v-if="open"
                :class="[
                    'absolute z-50 w-64 rounded-md border bg-popover p-4 text-popover-foreground shadow-md outline-none',
                    positionClass
                ]">

                <slot />
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: "HoverCard",

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
                    return `bottom-full mb-${this.sideOffset}`;

                case "left":
                    return `right-full mr-${this.sideOffset}`;

                case "right":
                    return `left-full ml-${this.sideOffset}`;

                default:
                    return `top-full mt-${this.sideOffset}`;
            }
        },
    },

    methods: {
        openCard() {
            this.open = true;
        },

        closeCard() {
            this.open = false;
        },
    },
};
</script>

<style scoped>
.hover-card-enter-active,
.hover-card-leave-active {
    transition: all 0.15s ease;
}

.hover-card-enter-from,
.hover-card-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>