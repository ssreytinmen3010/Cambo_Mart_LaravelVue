<template>
    <component
        :is="tag"
        :disabled="disabled"
        :class="[
            baseClass,
            variantClass,
            sizeClass
        ]">

        <slot />
    </component>
</template>

<script>
export default {
    name: "Button",

    props: {
        variant: {
            type: String,
            default: "default",
        },

        size: {
            type: String,
            default: "default",
        },

        tag: {
            type: String,
            default: "button",
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        baseClass() {
            return `
                inline-flex
                items-center
                justify-center
                gap-2
                whitespace-nowrap
                rounded-md
                text-sm
                font-medium
                cursor-pointer
                transition-colors
                focus-visible:outline-none
                focus-visible:ring-1
                focus-visible:ring-ring
                disabled:pointer-events-none
                disabled:opacity-50
                disabled:cursor-not-allowed
            `;
        },

        variantClass() {
            switch (this.variant) {

                case "destructive":
                    return "bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90";

                case "outline":
                    return "border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground";

                case "secondary":
                    return "bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80";

                case "ghost":
                    return "hover:bg-accent hover:text-accent-foreground";

                case "link":
                    return "text-primary underline-offset-4 hover:underline";

                default:
                    return "bg-primary text-primary-foreground shadow hover:bg-primary/90";
            }
        },

        sizeClass() {
            switch (this.size) {

                case "sm":
                    return "h-8 rounded-md px-3 text-xs";

                case "lg":
                    return "h-10 rounded-md px-8";

                case "icon":
                    return "h-9 w-9";

                default:
                    return "h-9 px-4 py-2";
            }
        },
    },
};
</script>