<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav aria-label="breadcrumb">
        <ol class="flex flex-wrap items-center gap-1.5 break-words text-sm text-muted-foreground sm:gap-2.5">
            <template v-for="(item, index) in items" :key="index">
                <li class="inline-flex items-center gap-1.5">
                    <Link
                        v-if="!item.current"
                        :href="item.href"
                        class="transition-colors hover:text-foreground"
                    >
                        {{ item.label }}
                    </Link>

                    <span
                        v-else
                        role="link"
                        aria-disabled="true"
                        aria-current="page"
                        class="font-normal text-foreground"
                    >
                        {{ item.label }}
                    </span>
                </li>

                <li
                    v-if="index < items.length - 1"
                    role="presentation"
                    aria-hidden="true"
                    class="[&>svg]:w-3.5 [&>svg]:h-3.5"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="w-3.5 h-3.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </li>
            </template>
        </ol>
    </nav>
</template>
