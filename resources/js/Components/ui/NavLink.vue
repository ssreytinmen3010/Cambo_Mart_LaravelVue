<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    activeClass: { type: String, default: 'text-primary font-semibold' },
    inactiveClass: { type: String, default: '' },
    exact: { type: Boolean, default: false },
});

const page = usePage();

const isActive = computed(() => {
    const current = (page.url ?? '/').split('?')[0];

    if (props.exact) {
        return current === props.href;
    }

    return current === props.href || current.startsWith(`${props.href}/`);
});

const classes = computed(() => (isActive.value ? props.activeClass : props.inactiveClass));
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
