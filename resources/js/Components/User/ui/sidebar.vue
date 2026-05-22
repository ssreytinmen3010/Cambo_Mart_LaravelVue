<template>
    <div class="flex min-h-screen w-full">

        <!-- Overlay Mobile -->
        <transition name="fade">
            <div
                v-if="mobileOpen"
                class="fixed inset-0 z-40 bg-black/50 md:hidden"
                @click="mobileOpen = false">
            </div>
        </transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed md:relative z-50 flex h-screen flex-col bg-sidebar text-sidebar-foreground border-r transition-all duration-300',
                collapsed ? 'w-16' : 'w-64',
                mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
                side === 'right' ? 'right-0 border-l border-r-0' : 'left-0'
            ]">

            <!-- Header -->
            <div class="flex items-center justify-between p-3 border-b">

                <slot name="header">
                    <h2
                        v-if="!collapsed"
                        class="text-lg font-semibold">

                        Sidebar
                    </h2>
                </slot>

                <!-- Toggle -->
                <button
                    @click="toggleSidebar"
                    class="rounded-md p-2 hover:bg-sidebar-accent transition">

                    ☰
                </button>
            </div>

            <!-- Search -->
            <div class="p-2">
                <input
                    type="text"
                    placeholder="Search..."
                    class="h-8 w-full rounded-md border bg-background px-2 text-sm outline-none" />
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-auto p-2">

                <!-- Groups -->
                <div
                    v-for="(group, groupIndex) in groups"
                    :key="groupIndex"
                    class="mb-4">

                    <!-- Group Label -->
                    <div
                        v-if="!collapsed"
                        class="mb-2 px-2 text-xs font-medium text-sidebar-foreground/70">

                        {{ group.label }}
                    </div>

                    <!-- Menu -->
                    <ul class="space-y-1">

                        <li
                            v-for="(item, itemIndex) in group.items"
                            :key="itemIndex"
                            class="relative">

                            <!-- Menu Button -->
                            <button
                                @click="selectItem(item)"
                                :class="[
                                    'flex w-full items-center gap-2 rounded-md p-2 text-left text-sm transition hover:bg-sidebar-accent',
                                    item.active
                                        ? 'bg-sidebar-accent font-medium'
                                        : ''
                                ]">

                                <!-- Icon -->
                                <span>
                                    {{ item.icon || '•' }}
                                </span>

                                <!-- Text -->
                                <span
                                    v-if="!collapsed"
                                    class="truncate">

                                    {{ item.label }}
                                </span>

                                <!-- Badge -->
                                <span
                                    v-if="item.badge && !collapsed"
                                    class="ml-auto rounded bg-sidebar-border px-1 text-xs">

                                    {{ item.badge }}
                                </span>
                            </button>

                            <!-- Sub Menu -->
                            <ul
                                v-if="item.children && !collapsed"
                                class="ml-6 mt-1 space-y-1 border-l pl-3">

                                <li
                                    v-for="(sub, subIndex) in item.children"
                                    :key="subIndex">

                                    <a
                                        :href="sub.href || '#'"
                                        class="flex h-7 items-center rounded-md px-2 text-sm hover:bg-sidebar-accent">

                                        {{ sub.label }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t p-3">
                <slot name="footer">
                    <p
                        v-if="!collapsed"
                        class="text-xs text-muted-foreground">

                        Sidebar Footer
                    </p>
                </slot>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 bg-background">
            <slot />
        </main>
    </div>
</template>

<script>
export default {
    name: "Sidebar",

    props: {
        side: {
            type: String,
            default: "left",
        },

        defaultCollapsed: {
            type: Boolean,
            default: false,
        },

        groups: {
            type: Array,
            default: () => [],
        },
    },

    emits: ["select"],

    data() {
        return {
            collapsed: this.defaultCollapsed,
            mobileOpen: false,
        };
    },

    methods: {
        toggleSidebar() {

            if (window.innerWidth < 768) {
                this.mobileOpen =
                    !this.mobileOpen;

            } else {
                this.collapsed =
                    !this.collapsed;
            }
        },

        selectItem(item) {
            this.$emit(
                "select",
                item
            );
        },
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>