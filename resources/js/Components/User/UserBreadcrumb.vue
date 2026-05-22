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
    <p class="text-xs text-muted-foreground">
        <template v-for="(item, index) in items" :key="index">
            <Link v-if="item.href" :href="item.href" class="hover:text-primary">{{ item.label }}</Link>
            <span v-else>{{ item.label }}</span>
            <span v-if="index < items.length - 1"> / </span>
        </template>
    </p>
</template>
