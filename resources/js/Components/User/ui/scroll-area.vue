<template>
    <div
        :class="[
            'relative overflow-hidden',
            className
        ]">

        <!-- Viewport -->
        <div
            ref="viewport"
            class="h-full w-full rounded-[inherit] overflow-auto"
            @scroll="handleScroll">

            <slot />
        </div>

        <!-- Vertical Scrollbar -->
        <div
            v-if="showVertical"
            class="absolute top-0 right-0 h-full w-2.5 p-[1px]">

            <div
                class="relative flex-1 rounded-full bg-border"
                :style="verticalThumbStyle">
            </div>
        </div>

        <!-- Horizontal Scrollbar -->
        <div
            v-if="showHorizontal"
            class="absolute bottom-0 left-0 h-2.5 w-full p-[1px]">

            <div
                class="relative h-full rounded-full bg-border"
                :style="horizontalThumbStyle">
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ScrollArea",

    props: {
        className: {
            type: String,
            default: "",
        },
    },

    data() {
        return {
            verticalThumbStyle: {},
            horizontalThumbStyle: {},

            showVertical: false,
            showHorizontal: false,
        };
    },

    mounted() {
        this.updateScrollbars();

        window.addEventListener(
            "resize",
            this.updateScrollbars
        );
    },

    beforeUnmount() {
        window.removeEventListener(
            "resize",
            this.updateScrollbars
        );
    },

    methods: {
        handleScroll() {
            this.updateScrollbars();
        },

        updateScrollbars() {

            const el = this.$refs.viewport;

            if (!el) return;

            // Vertical
            this.showVertical =
                el.scrollHeight > el.clientHeight;

            const verticalHeight =
                (el.clientHeight / el.scrollHeight) *
                el.clientHeight;

            const verticalTop =
                (el.scrollTop / el.scrollHeight) *
                el.clientHeight;

            this.verticalThumbStyle = {
                height: `${verticalHeight}px`,
                transform: `translateY(${verticalTop}px)`,
            };

            // Horizontal
            this.showHorizontal =
                el.scrollWidth > el.clientWidth;

            const horizontalWidth =
                (el.clientWidth / el.scrollWidth) *
                el.clientWidth;

            const horizontalLeft =
                (el.scrollLeft / el.scrollWidth) *
                el.clientWidth;

            this.horizontalThumbStyle = {
                width: `${horizontalWidth}px`,
                transform: `translateX(${horizontalLeft}px)`,
            };
        },
    },
};
</script>