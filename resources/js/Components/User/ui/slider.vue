<template>
    <div
        class="relative flex w-full touch-none select-none items-center">

        <!-- Track -->
        <div
            class="relative h-1.5 w-full grow overflow-hidden rounded-full bg-primary/20">

            <!-- Range -->
            <div
                class="absolute h-full bg-primary"
                :style="{
                    width: `${percent}%`
                }">
            </div>
        </div>

        <!-- Thumb -->
        <div
            class="absolute block h-4 w-4 rounded-full border border-primary/50 bg-background shadow transition-colors cursor-pointer"
            :style="{
                left: `calc(${percent}% - 8px)`
            }"
            @mousedown="startDrag">
        </div>
    </div>
</template>

<script>
export default {
    name: "Slider",

    props: {
        modelValue: {
            type: Number,
            default: 0,
        },

        min: {
            type: Number,
            default: 0,
        },

        max: {
            type: Number,
            default: 100,
        },

        step: {
            type: Number,
            default: 1,
        },
    },

    emits: ["update:modelValue"],

    computed: {
        percent() {
            return (
                ((this.modelValue - this.min) /
                    (this.max - this.min)) * 100
            );
        },
    },

    methods: {
        startDrag(event) {

            const slider =
                this.$el;

            const move = (e) => {

                const rect =
                    slider.getBoundingClientRect();

                let percent =
                    ((e.clientX - rect.left) / rect.width);

                percent =
                    Math.max(0, Math.min(1, percent));

                let value =
                    this.min +
                    percent * (this.max - this.min);

                value =
                    Math.round(value / this.step) * this.step;

                this.$emit(
                    "update:modelValue",
                    value
                );
            };

            const up = () => {
                window.removeEventListener("mousemove", move);
                window.removeEventListener("mouseup", up);
            };

            window.addEventListener("mousemove", move);
            window.addEventListener("mouseup", up);

            move(event);
        },
    },
};
</script>