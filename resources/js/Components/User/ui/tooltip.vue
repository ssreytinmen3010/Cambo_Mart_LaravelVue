<template>
    <div
        class="relative inline-block"
        @mouseenter="showTooltip"
        @mouseleave="hideTooltip">

        <!-- Trigger -->
        <div>
            <slot />
        </div>

        <!-- Tooltip -->
        <transition name="tooltip">

            <div
                v-if="visible"
                :class="[
                    'absolute z-50 overflow-hidden rounded-md bg-primary px-3 py-1.5 text-xs text-primary-foreground shadow-md whitespace-nowrap',
                    positionClass,
                    className
                ]">

                {{ text }}
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: "Tooltip",

    props: {
        text: {
            type: String,
            default: "",
        },

        side: {
            type: String,
            default: "top",
        },

        sideOffset: {
            type: Number,
            default: 4,
        },

        className: {
            type: String,
            default: "",
        },
    },

    data() {
        return {
            visible: false,
        };
    },

    computed: {
        positionClass() {

            switch (this.side) {

                case "bottom":
                    return `top-full mt-${this.sideOffset}`;

                case "left":
                    return `right-full mr-${this.sideOffset} top-1/2 -translate-y-1/2`;

                case "right":
                    return `left-full ml-${this.sideOffset} top-1/2 -translate-y-1/2`;

                default:
                    return `bottom-full mb-${this.sideOffset} left-1/2 -translate-x-1/2`;
            }
        },
    },

    methods: {
        showTooltip() {
            this.visible = true;
        },

        hideTooltip() {
            this.visible = false;
        },
    },
};
</script>

<style scoped>
.tooltip-enter-active,
.tooltip-leave-active {
    transition: all 0.15s ease;
}

.tooltip-enter-from,
.tooltip-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>