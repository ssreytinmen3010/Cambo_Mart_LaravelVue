<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="flex flex-col items-center mb-8">
            <img src="@img/Logo.png" alt="CamboMart Logo" class="w-12 h-12 mr-2" />
            <h2 class="text-2xl font-bold text-gray-800">Join CamboMart</h2>
            <p class="text-sm text-gray-400">Create an account to start shopping</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full border-gray-200 rounded-xl px-4 py-3 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.name"
                    placeholder="Full Name"
                    required
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-gray-200 rounded-xl px-4 py-3 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.email"
                    placeholder="Email Address"
                    required
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4 relative">
                <input
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="form.password"
                    placeholder="Password"
                    required
                    class="mt-1 block w-full border-gray-200 rounded-xl px-4 py-3 pr-12
                        focus:border-emerald-500 focus:ring-emerald-500"
                />

                <!-- Eye Icon -->
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-emerald-500"
                >
                    <!-- Eye Open -->
                    <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5
                                c4.478 0 8.268 2.943 9.542 7
                                -1.274 4.057-5.064 7-9.542 7
                                -4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>

                    <!-- Eye Closed -->
                    <svg v-else xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 3l18 18"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.584 10.586a2 2 0 002.828 2.828"/>
                    </svg>
                </button>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 relative">
                <input
                    id="password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    v-model="form.password_confirmation"
                    placeholder="Confirm Password"
                    required
                    class="mt-1 block w-full border-gray-200 rounded-xl px-4 py-3 pr-12
                        focus:border-emerald-500 focus:ring-emerald-500"
                />

                <!-- Eye Icon -->
                <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-emerald-500"
                >
                    <!-- Eye Open -->
                    <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5
                                c4.478 0 8.268 2.943 9.542 7
                                -1.274 4.057-5.064 7-9.542 7
                                -4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>

                    <!-- Eye Closed -->
                    <svg v-else xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 3l18 18"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.584 10.586a2 2 0 002.828 2.828"/>
                    </svg>
                </button>

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>


            <div class="mt-6">
                <PrimaryButton 
                    class="w-full flex justify-center py-3 bg-green-500 hover:bg-emerald-600 rounded-xl transition duration-150"
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>

            <div class="flex items-center justify-center mt-6">
                <Link
                    :href="route('login')"
                    class="text-sm font-semibold text-emerald-500 hover:text-emerald-600"
                >
                    Already registered? Sign In
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>