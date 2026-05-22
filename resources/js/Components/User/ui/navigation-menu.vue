<template>
    <nav
        class="relative z-10 flex max-w-max flex-1 items-center justify-center">

        <!-- Menu List -->
        <ul
            class="group flex flex-1 list-none items-center justify-center space-x-1">

            <li
                v-for="(item, index) in items"
                :key="index"
                class="relative">

                <!-- Trigger -->
                <button
                    @click="toggleMenu(index)"
                    :class="[
                        'group inline-flex h-9 w-max items-center justify-center rounded-md bg-background px-4 py-2 text-sm font-medium cursor-pointer transition-colors',
                        openIndex === index
                            ? 'bg-accent/50 text-accent-foreground'
                            : 'hover:bg-accent hover:text-accent-foreground'
                    ]">

                    {{ item.label }}

                    <!-- Arrow -->
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        :class="[
                            'relative top-[1px] ml-1 h-3 w-3 transition duration-300',
                            openIndex === index
                                ? 'rotate-180'
                                : ''
                        ]">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m19 9-7 7-7-7" />
                    </svg>
                </button>

                <!-- Content -->
                <transition name="nav-menu">

                    <div
                        v-if="openIndex === index"
                        class="absolute left-0 top-full mt-2 min-w-[220px] overflow-hidden rounded-md border bg-popover p-2 text-popover-foreground shadow-md">

                        <!-- Links -->
                        <a
                            v-for="(link, linkIndex) in item.links"
                            :key="linkIndex"
                            :href="link.href"
                            class="block rounded-md px-3 py-2 text-sm transition hover:bg-accent hover:text-accent-foreground">

                            {{ link.label }}
                        </a>
                    </div>
                </transition>
            </li>
        </ul>
    </nav>
</template>

<script>
export default {
    name: "NavigationMenu",

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
        toggleMenu(index) {
            this.openIndex =
                this.openIndex === index
                    ? null
                    : index;
        },
    },
};
</script>

<style scoped>
.nav-menu-enter-active,
.nav-menu-leave-active {
    transition: all 0.2s ease;
}

.nav-menu-enter-from,
.nav-menu-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>