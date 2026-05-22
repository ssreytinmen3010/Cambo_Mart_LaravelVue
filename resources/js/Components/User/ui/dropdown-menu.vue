<template>
    <div class="relative inline-block">

        <!-- Trigger -->
        <div @click="toggleMenu">
            <slot name="trigger">
                <button
                    class="rounded-md border px-4 py-2 text-sm hover:bg-accent transition">

                    Open Menu
                </button>
            </slot>
        </div>

        <!-- Dropdown -->
        <transition name="dropdown">

            <div
                v-if="open"
                class="absolute z-50 mt-2 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md">

                <!-- Label -->
                <div
                    v-if="label"
                    class="px-2 py-1.5 text-sm font-semibold">

                    {{ label }}
                </div>

                <!-- Separator -->
                <div
                    v-if="label"
                    class="-mx-1 my-1 h-px bg-muted">
                </div>

                <!-- Items -->
                <div
                    v-for="(item, index) in items"
                    :key="index">

                    <!-- Separator -->
                    <div
                        v-if="item.separator"
                        class="-mx-1 my-1 h-px bg-muted">
                    </div>

                    <!-- Normal Item -->
                    <button
                        v-else
                        @click="selectItem(item)"
                        :disabled="item.disabled"
                        :class="[
                            'relative flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm transition-colors',
                            item.disabled
                                ? 'pointer-events-none opacity-50'
                                : 'hover:bg-accent hover:text-accent-foreground'
                        ]">

                        <!-- Checkbox -->
                        <span
                            v-if="item.type === 'checkbox'"
                            class="w-4">

                            {{ item.checked ? '✔' : '' }}
                        </span>

                        <!-- Radio -->
                        <span
                            v-if="item.type === 'radio'"
                            class="w-4">

                            {{ item.checked ? '●' : '○' }}
                        </span>

                        <!-- Icon -->
                        <span v-if="item.icon">
                            {{ item.icon }}
                        </span>

                        <!-- Label -->
                        <span>
                            {{ item.label }}
                        </span>

                        <!-- Shortcut -->
                        <span
                            v-if="item.shortcut"
                            class="ml-auto text-xs tracking-widest opacity-60">

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
    name: "DropdownMenu",

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
        };
    },

    methods: {
        toggleMenu() {
            this.open = !this.open;
        },

        selectItem(item) {
            this.$emit("select", item);

            this.open = false;
        },
    },
};
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>