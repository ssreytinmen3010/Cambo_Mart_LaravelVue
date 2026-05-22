<template>
    <div
        class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full">

        <!-- Image -->
        <img
            v-if="src && !imageError"
            :src="src"
            :alt="alt"
            class="aspect-square h-full w-full object-cover"
            @error="imageError = true" />

        <!-- Fallback -->
        <div
            v-else
            class="flex h-full w-full items-center justify-center rounded-full bg-muted">

            <slot>
                {{ fallback }}
            </slot>
        </div>
    </div>
</template>

<script>
export default {
    name: "Avatar",

    props: {
        src: {
            type: String,
            default: "",
        },

        alt: {
            type: String,
            default: "Avatar",
        },

        fallback: {
            type: String,
            default: "U",
        },
    },

    data() {
        return {
            imageError: false,
        };
    },
};
</script>