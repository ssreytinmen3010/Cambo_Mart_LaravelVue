<template>
    <div class="accordion">
        <div
            v-for="(item, index) in items"
            :key="index"
            class="border-b">

            <!-- Trigger -->
            <button
                @click="toggle(index)"
                class="flex w-full items-center justify-between py-4 text-sm font-medium cursor-pointer transition-all hover:underline text-left">

                {{ item.title }}

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200"
                    :class="openIndex === index ? 'rotate-180' : ''">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m19 9-7 7-7-7" />
                </svg>
            </button>

            <!-- Content -->
            <transition name="accordion">

                <div
                    v-show="openIndex === index"
                    class="overflow-hidden text-sm">

                    <div class="pb-4 pt-0">
                        {{ item.content }}
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script>
export default {
    name: "Accordion",

    props: {
        items: {
            type: Array,
            default: () => [],
        },
    },

    data() {
        return {
            openIndex: null,
        };
    },

    methods: {
        toggle(index) {
            this.openIndex =
                this.openIndex === index
                    ? null
                    : index;
        },
    },
};
</script>

<style scoped>
.accordion-enter-active,
.accordion-leave-active {
    transition: all 0.25s ease;
    overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
    opacity: 0;
    max-height: 0;
}

.accordion-enter-to,
.accordion-leave-from {
    opacity: 1;
    max-height: 500px;
}
</style>