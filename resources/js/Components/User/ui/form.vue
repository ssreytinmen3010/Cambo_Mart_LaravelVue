<template>
    <form
        @submit.prevent="submitForm"
        class="space-y-4">

        <!-- Form Item -->
        <div
            v-for="(field, index) in fields"
            :key="index"
            class="space-y-2">

            <!-- Label -->
            <label
                :for="field.name"
                :class="[
                    'text-sm font-medium',
                    errors[field.name]
                        ? 'text-destructive'
                        : ''
                ]">

                {{ field.label }}
            </label>

            <!-- Input -->
            <input
                v-model="form[field.name]"
                :id="field.name"
                :type="field.type || 'text'"
                :placeholder="field.placeholder"
                class="flex h-10 w-full rounded-md border bg-background px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-ring"
                :aria-invalid="!!errors[field.name]" />

            <!-- Description -->
            <p
                v-if="field.description"
                class="text-[0.8rem] text-muted-foreground">

                {{ field.description }}
            </p>

            <!-- Error Message -->
            <p
                v-if="errors[field.name]"
                class="text-[0.8rem] font-medium text-destructive">

                {{ errors[field.name] }}
            </p>
        </div>

        <!-- Submit -->
        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">

            Submit
        </button>
    </form>
</template>

<script>
export default {
    name: "Form",

    props: {
        fields: {
            type: Array,
            default: () => [],
        },
    },

    emits: ["submit"],

    data() {
        return {
            form: {},
            errors: {},
        };
    },

    created() {
        this.fields.forEach(field => {
            this.form[field.name] = field.default || "";
        });
    },

    methods: {
        validate() {
            this.errors = {};

            this.fields.forEach(field => {

                if (
                    field.required &&
                    !this.form[field.name]
                ) {
                    this.errors[field.name] =
                        `${field.label} is required`;
                }
            });

            return Object.keys(this.errors).length === 0;
        },

        submitForm() {
            if (!this.validate()) return;

            this.$emit("submit", this.form);
        },
    },
};
</script>