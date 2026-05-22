<template>
    <nav
        role="navigation"
        aria-label="pagination"
        class="mx-auto flex w-full justify-center">

        <ul
            class="flex flex-row items-center gap-1">

            <!-- Previous -->
            <li>
                <button
                    @click="changePage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="inline-flex items-center justify-center gap-1 rounded-md px-3 py-2 text-sm transition hover:bg-accent disabled:opacity-50">

                    <!-- Left Icon -->
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-4 w-4">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m15 19-7-7 7-7" />
                    </svg>

                    <span>Previous</span>
                </button>
            </li>

            <!-- Pages -->
            <li
                v-for="page in totalPages"
                :key="page">

                <button
                    @click="changePage(page)"
                    :class="[
                        'inline-flex h-9 w-9 items-center justify-center rounded-md text-sm transition',
                        currentPage === page
                            ? 'border bg-background shadow-sm'
                            : 'hover:bg-accent'
                    ]">

                    {{ page }}
                </button>
            </li>

            <!-- Ellipsis -->
            <li
                v-if="totalPages > maxVisiblePages"
                class="flex h-9 w-9 items-center justify-center">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="h-4 w-4">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6.75 12h.008v.008H6.75V12Zm5.25 0h.008v.008H12V12Zm5.25 0h.008v.008H17.25V12Z" />
                </svg>
            </li>

            <!-- Next -->
            <li>
                <button
                    @click="changePage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="inline-flex items-center justify-center gap-1 rounded-md px-3 py-2 text-sm transition hover:bg-accent disabled:opacity-50">

                    <span>Next</span>

                    <!-- Right Icon -->
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-4 w-4">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m9 5 7 7-7 7" />
                    </svg>
                </button>
            </li>
        </ul>
    </nav>
</template>

<script>
export default {
    name: "Pagination",

    props: {
        modelValue: {
            type: Number,
            default: 1,
        },

        totalPages: {
            type: Number,
            default: 1,
        },

        maxVisiblePages: {
            type: Number,
            default: 5,
        },
    },

    emits: ["update:modelValue"],

    computed: {
        currentPage() {
            return this.modelValue;
        },
    },

    methods: {
        changePage(page) {

            if (
                page < 1 ||
                page > this.totalPages
            ) return;

            this.$emit(
                "update:modelValue",
                page
            );
        },
    },
};
</script>