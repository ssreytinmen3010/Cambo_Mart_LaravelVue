<template>
    <div
        role="alert"
        :class="[
            'relative w-full rounded-lg border px-4 py-3 text-sm',
            variant === 'destructive'
                ? 'border-destructive/50 text-destructive'
                : 'bg-background text-foreground'
        ]">

        <!-- Title -->
        <h5
            v-if="title"
            class="mb-1 font-medium leading-none tracking-tight">

            {{ title }}
        </h5>

        <!-- Description -->
        <div class="text-sm leading-relaxed">
            <slot>
                {{ description }}
            </slot>
        </div>
    </div>
</template>

<script>
export default {
    name: "Alert",

    props: {
        variant: {
            type: String,
            default: "default",
        },

        title: {
            type: String,
            default: "",
        },

        description: {
            type: String,
            default: "",
        },
    },
};
</script>