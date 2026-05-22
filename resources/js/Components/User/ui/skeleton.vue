<template>
    <div
        :class="[
            'animate-pulse rounded-md bg-primary/10',
            className
        ]">
    </div>
</template>

<script>
export default {
    name: "Skeleton",

    props: {
        className: {
            type: String,
            default: "",
        },
    },
};
</script>