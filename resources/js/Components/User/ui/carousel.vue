<template>
    <div
        class="relative"
        role="region"
        aria-roledescription="carousel"
        tabindex="0"
        @keydown.left="prevSlide"
        @keydown.right="nextSlide">

        <!-- Content -->
        <div class="overflow-hidden">

            <div
                class="flex transition-transform duration-300 ease-in-out"
                :class="orientation === 'vertical'
                    ? 'flex-col'
                    : ''"
                :style="carouselStyle">

                <div
                    v-for="(item, index) in items"
                    :key="index"
                    role="group"
                    aria-roledescription="slide"
                    class="min-w-full shrink-0 grow-0">

                    <slot
                        name="item"
                        :item="item"
                        :index="index">

                        <!-- Default -->
                        <div class="p-4">
                            {{ item }}
                        </div>
                    </slot>
                </div>
            </div>
        </div>

        <!-- Previous -->
        <button
            @click="prevSlide"
            :disabled="currentIndex === 0"
            :class="[
                'absolute h-8 w-8 rounded-full border bg-background shadow flex items-center justify-center',
                orientation === 'horizontal'
                    ? '-left-12 top-1/2 -translate-y-1/2'
                    : '-top-12 left-1/2 -translate-x-1/2 rotate-90'
            ]">

            ←
        </button>

        <!-- Next -->
        <button
            @click="nextSlide"
            :disabled="currentIndex === items.length - 1"
            :class="[
                'absolute h-8 w-8 rounded-full border bg-background shadow flex items-center justify-center',
                orientation === 'horizontal'
                    ? '-right-12 top-1/2 -translate-y-1/2'
                    : '-bottom-12 left-1/2 -translate-x-1/2 rotate-90'
            ]">

            →
        </button>
    </div>
</template>

<script>
export default {
    name: "Carousel",

    props: {
        items: {
            type: Array,
            default: () => [],
        },

        orientation: {
            type: String,
            default: "horizontal",
        },
    },

    data() {
        return {
            currentIndex: 0,
        };
    },

    computed: {
        carouselStyle() {
            if (this.orientation === "vertical") {
                return {
                    transform: `translateY(-${this.currentIndex * 100}%)`,
                };
            }

            return {
                transform: `translateX(-${this.currentIndex * 100}%)`,
            };
        },
    },

    methods: {
        prevSlide() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
        },

        nextSlide() {
            if (this.currentIndex < this.items.length - 1) {
                this.currentIndex++;
            }
        },
    },
};
</script>