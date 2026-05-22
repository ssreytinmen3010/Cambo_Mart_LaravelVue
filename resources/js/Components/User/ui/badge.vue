<template>
    <div
        :class="[
            'inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
            badgeClass
        ]">

        <slot>
            {{ text }}
        </slot>
    </div>
</template>

<script>
export default {
    name: "Badge",

    props: {
        variant: {
            type: String,
            default: "default",
        },

        text: {
            type: String,
            default: "",
        },
    },

    computed: {
        badgeClass() {
            switch (this.variant) {
                case "secondary":
                    return "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80";

                case "destructive":
                    return "border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80";

                case "outline":
                    return "text-foreground";

                default:
                    return "border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80";
            }
        },
    },
};
</script>