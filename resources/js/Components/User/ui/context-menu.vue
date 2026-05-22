<template>
    <div
        class="relative inline-block"
        @contextmenu.prevent="openMenu"
        @click="closeMenu">

        <!-- Trigger -->
        <div>
            <slot />
        </div>

        <!-- Context Menu -->
        <transition name="fade">

            <div
                v-if="open"
                class="absolute z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md"
                :style="{
                    top: `${position.y}px`,
                    left: `${position.x}px`
                }">

                <!-- Label -->
                <div
                    v-if="label"
                    class="px-2 py-1.5 text-sm font-semibold text-foreground">

                    {{ label }}
                </div>

                <!-- Separator -->
                <div
                    v-if="label"
                    class="-mx-1 my-1 h-px bg-border">
                </div>

                <!-- Items -->
                <div
                    v-for="(item, index) in items"
                    :key="index">

                    <!-- Separator -->
                    <div
                        v-if="item.separator"
                        class="-mx-1 my-1 h-px bg-border">
                    </div>

                    <!-- Item -->
                    <button
                        v-else
                        @click="selectItem(item)"
                        :disabled="item.disabled"
                        :class="[
                            'relative flex w-full cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition',
                            item.disabled
                                ? 'pointer-events-none opacity-50'
                                : 'hover:bg-accent hover:text-accent-foreground'
                        ]">

                        <!-- Checkbox -->
                        <span
                            v-if="item.type === 'checkbox'"
                            class="mr-2">

                            {{ item.checked ? '✔' : '' }}
                        </span>

                        <!-- Radio -->
                        <span
                            v-if="item.type === 'radio'"
                            class="mr-2">

                            {{ item.checked ? '●' : '○' }}
                        </span>

                        <!-- Label -->
                        <span>
                            {{ item.label }}
                        </span>

                        <!-- Shortcut -->
                        <span
                            v-if="item.shortcut"
                            class="ml-auto text-xs tracking-widest text-muted-foreground">

                            {{ item.shortcut }}
                        </span>

                        <!-- Submenu -->
                        <span
                            v-if="item.submenu"
                            class="ml-auto">

                            ▶
                        </span>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: "ContextMenu",

    props: {
        items: {
            type: Array,
            default: () => [],
        },

        label: {
            type: String,
            default: "",
        },
    },

    emits: ["select"],

    data() {
        return {
            open: false,

            position: {
                x: 0,
                y: 0,
            },
        };
    },

    methods: {
        openMenu(event) {
            this.position = {
                x: event.clientX,
                y: event.clientY,
            };

            this.open = true;
        },

        closeMenu() {
            this.open = false;
        },

        selectItem(item) {
            this.$emit("select", item);

            this.closeMenu();
        },
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>