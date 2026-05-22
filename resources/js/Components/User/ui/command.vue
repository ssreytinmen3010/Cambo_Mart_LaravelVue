<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <!-- Dialog -->
        <div
            class="w-full max-w-lg overflow-hidden rounded-md bg-popover text-popover-foreground shadow-xl">

            <!-- Input -->
            <div class="flex items-center border-b px-3">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="mr-2 h-4 w-4 shrink-0 opacity-50">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 5.5 5.5a7.5 7.5 0 0 0 11.15 11.15Z" />
                </svg>

                <input
                    v-model="search"
                    type="text"
                    placeholder="Type a command or search..."
                    class="flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground" />
            </div>

            <!-- List -->
            <div class="max-h-[300px] overflow-y-auto overflow-x-hidden p-1">

                <!-- Empty -->
                <div
                    v-if="filteredItems.length === 0"
                    class="py-6 text-center text-sm">

                    No results found.
                </div>

                <!-- Groups -->
                <div
                    v-for="(group, groupIndex) in groupedItems"
                    :key="groupIndex"
                    class="overflow-hidden p-1 text-foreground">

                    <!-- Heading -->
                    <div
                        class="px-2 py-1.5 text-xs font-medium text-muted-foreground">

                        {{ group.label }}
                    </div>

                    <!-- Items -->
                    <div
                        v-for="(item, index) in group.items"
                        :key="index"
                        @click="selectItem(item)"
                        class="relative flex cursor-pointer gap-2 select-none items-center rounded-sm px-2 py-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground transition">

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
                            class="ml-auto text-xs tracking-widest text-muted-foreground">

                            {{ item.shortcut }}
                        </span>
                    </div>

                    <!-- Separator -->
                    <div
                        v-if="groupIndex < groupedItems.length - 1"
                        class="-mx-1 my-1 h-px bg-border">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "CommandDialog",

    props: {
        open: {
            type: Boolean,
            default: false,
        },

        groups: {
            type: Array,
            default: () => [],
        },
    },

    emits: ["select", "close"],

    data() {
        return {
            search: "",
        };
    },

    computed: {
        filteredItems() {
            return this.groups.flatMap(group =>
                group.items.filter(item =>
                    item.label
                        .toLowerCase()
                        .includes(this.search.toLowerCase())
                )
            );
        },

        groupedItems() {
            return this.groups.map(group => ({
                ...group,
                items: group.items.filter(item =>
                    item.label
                        .toLowerCase()
                        .includes(this.search.toLowerCase())
                ),
            })).filter(group => group.items.length > 0);
        },
    },

    methods: {
        selectItem(item) {
            this.$emit("select", item);
        },
    },
};
</script>