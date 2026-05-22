<template>
    <div class="w-full">

        <!-- Trigger -->
        <div @click="toggle">
            <slot
                name="trigger"
                :open="open">
            </slot>
        </div>

        <!-- Content -->
        <transition name="collapsible">

            <div
                v-show="open"
                class="overflow-hidden">

                <slot
                    name="content"
                    :open="open">
                </slot>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: "Collapsible",

    props: {
        defaultOpen: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            open: this.defaultOpen,
        };
    },

    methods: {
        toggle() {
            this.open = !this.open;
        },
    },
};
</script>

<style scoped>
.collapsible-enter-active,
.collapsible-leave-active {
    transition: all 0.25s ease;
}

.collapsible-enter-from,
.collapsible-leave-to {
    opacity: 0;
    max-height: 0;
}

.collapsible-enter-to,
.collapsible-leave-from {
    opacity: 1;
    max-height: 500px;
}
</style>