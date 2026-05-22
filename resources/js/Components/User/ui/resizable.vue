<template>
    <div
        :class="[
            'flex h-full w-full',
            direction === 'vertical'
                ? 'flex-col'
                : 'flex-row'
        ]">

        <!-- Panel 1 -->
        <div
            class="overflow-auto"
            :style="panelStyle(panel1Size)">

            <slot name="panel1" />
        </div>

        <!-- Handle -->
        <div
            @mousedown="startResize"
            :class="[
                direction === 'vertical'
                    ? 'relative flex h-px w-full items-center justify-center bg-border cursor-row-resize'
                    : 'relative flex w-px items-center justify-center bg-border cursor-col-resize'
            ]">

            <!-- Handle Icon -->
            <div
                v-if="withHandle"
                class="z-10 flex h-4 w-3 items-center justify-center rounded-sm border bg-border">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="h-2.5 w-2.5"
                    :class="direction === 'vertical'
                        ? 'rotate-90'
                        : ''">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 9h.01M8 15h.01M12 9h.01M12 15h.01M16 9h.01M16 15h.01" />
                </svg>
            </div>
        </div>

        <!-- Panel 2 -->
        <div
            class="overflow-auto flex-1"
            :style="panelStyle(100 - panel1Size)">

            <slot name="panel2" />
        </div>
    </div>
</template>

<script>
export default {
    name: "ResizablePanelGroup",

    props: {
        direction: {
            type: String,
            default: "horizontal",
        },

        withHandle: {
            type: Boolean,
            default: false,
        },

        defaultSize: {
            type: Number,
            default: 50,
        },
    },

    data() {
        return {
            panel1Size: this.defaultSize,
            resizing: false,
        };
    },

    methods: {
        panelStyle(size) {

            if (this.direction === "vertical") {
                return {
                    height: `${size}%`,
                };
            }

            return {
                width: `${size}%`,
            };
        },

        startResize(event) {
            this.resizing = true;

            const startX = event.clientX;
            const startY = event.clientY;
            const startSize = this.panel1Size;

            const onMove = (e) => {

                if (!this.resizing) return;

                if (this.direction === "horizontal") {

                    const delta =
                        ((e.clientX - startX) /
                            window.innerWidth) * 100;

                    this.panel1Size =
                        Math.min(
                            90,
                            Math.max(10, startSize + delta)
                        );

                } else {

                    const delta =
                        ((e.clientY - startY) /
                            window.innerHeight) * 100;

                    this.panel1Size =
                        Math.min(
                            90,
                            Math.max(10, startSize + delta)
                        );
                }
            };

            const onUp = () => {
                this.resizing = false;

                window.removeEventListener("mousemove", onMove);
                window.removeEventListener("mouseup", onUp);
            };

            window.addEventListener("mousemove", onMove);
            window.addEventListener("mouseup", onUp);
        },
    },
};
</script>