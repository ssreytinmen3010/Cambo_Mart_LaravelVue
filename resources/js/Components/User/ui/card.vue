<template>
    <div
        class="rounded-xl border bg-card text-card-foreground shadow">

        <!-- Header -->
        <div
            v-if="$slots.header || title || description"
            class="flex flex-col space-y-1.5 p-6">

            <!-- Title -->
            <div
                v-if="title"
                class="font-semibold leading-none tracking-tight">

                {{ title }}
            </div>

            <!-- Description -->
            <div
                v-if="description"
                class="text-sm text-muted-foreground">

                {{ description }}
            </div>

            <!-- Custom Header Slot -->
            <slot name="header" />
        </div>

        <!-- Content -->
        <div class="p-6 pt-0">
            <slot />
        </div>

        <!-- Footer -->
        <div
            v-if="$slots.footer"
            class="flex items-center p-6 pt-0">

            <slot name="footer" />
        </div>
    </div>
</template>

<script>
export default {
    name: "Card",

    props: {
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