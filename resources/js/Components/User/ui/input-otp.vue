<template>
    <div
        class="flex items-center gap-2"
        :class="{ 'opacity-50': disabled }">

        <!-- OTP Slots -->
        <div class="flex items-center">

            <div
                v-for="(digit, index) in otpArray"
                :key="index"
                :class="[
                    'relative flex h-9 w-9 items-center justify-center border-y border-r border-input text-sm shadow-sm transition-all first:rounded-l-md first:border-l last:rounded-r-md',
                    activeIndex === index
                        ? 'z-10 ring-1 ring-ring'
                        : ''
                ]">

                <input
                    type="text"
                    maxlength="1"
                    :disabled="disabled"
                    v-model="otpArray[index]"
                    @focus="activeIndex = index"
                    @input="handleInput(index)"
                    class="absolute inset-0 w-full h-full text-center bg-transparent outline-none" />

                <span>
                    {{ digit }}
                </span>
            </div>
        </div>

        <!-- Separator -->
        <div
            v-if="showSeparator"
            role="separator"
            class="flex items-center justify-center">

            −
        </div>
    </div>
</template>

<script>
export default {
    name: "InputOTP",

    props: {
        modelValue: {
            type: String,
            default: "",
        },

        length: {
            type: Number,
            default: 6,
        },

        disabled: {
            type: Boolean,
            default: false,
        },

        showSeparator: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["update:modelValue"],

    data() {
        return {
            otpArray: Array(this.length).fill(""),
            activeIndex: 0,
        };
    },

    mounted() {
        if (this.modelValue) {
            this.otpArray = this.modelValue
                .split("")
                .slice(0, this.length);
        }
    },

    methods: {
        handleInput(index) {

            const otp = this.otpArray.join("");

            this.$emit(
                "update:modelValue",
                otp
            );

            // Auto next
            if (
                this.otpArray[index] &&
                index < this.length - 1
            ) {
                const next =
                    this.$el.querySelectorAll("input")[index + 1];

                next?.focus();
            }
        },
    },
};
</script>