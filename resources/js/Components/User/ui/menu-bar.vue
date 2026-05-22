<template>
    <div
        class="flex h-9 items-center space-x-1 rounded-md border bg-background p-1 shadow-sm">

        <!-- Menus -->
        <div
            v-for="(menu, menuIndex) in menus"
            :key="menuIndex"
            class="relative">

            <!-- Trigger -->
            <button
                @click="toggleMenu(menuIndex)"
                class="flex items-center rounded-sm px-3 py-1 text-sm font-medium outline-none transition hover:bg-accent hover:text-accent-foreground">

                {{ menu.label }}
            </button>

            <!-- Content -->
            <transition name="menubar">

                <div
                    v-if="openMenu === menuIndex"
                    class="absolute left-0 top-full z-50 mt-2 min-w-[12rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md">

                    <!-- Items -->
                    <div
                        v-for="(item, itemIndex) in menu.items"
                        :key="itemIndex">

                        <!-- Separator -->
                        <div
                            v-if="item.separator"
                            class="-mx-1 my-1 h-px bg-muted">
                        </div>

                        <!-- Label -->
                        <div
                            v-else-if="item.type === 'label'"
                            class="px-2 py-1.5 text-sm font-semibold">

                            {{ item.label }}
                        </div>

                        <!-- Normal Item -->
                        <button
                            v-else
                            @click="selectItem(item)"
                            :disabled="item.disabled"
                            :class="[
                                'relative flex w-full items-center rounded-sm px-2 py-1.5 text-sm outline-none transition',
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
    </div>
</template>

<script>
export default {
    name: "Menubar",

    props: {
        menus: {
            type: Array,
            default: () => [],
        },
    },

    emits: ["select"],

    data() {
        return {
            openMenu: null,
        };
    },

    methods: {
        toggleMenu(index) {
            this.openMenu =
                this.openMenu === index
                    ? null
                    : index;
        },

        selectItem(item) {
            this.$emit("select", item);

            this.openMenu = null;
        },
    },
};
</script>

<style scoped>
.menubar-enter-active,
.menubar-leave-active {
    transition: all 0.15s ease;
}

.menubar-enter-from,
.menubar-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>