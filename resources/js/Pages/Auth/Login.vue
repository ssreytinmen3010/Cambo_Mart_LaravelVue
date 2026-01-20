<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'
const showPassword = ref(false)

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="flex flex-col items-center mb-8">
            <div class="flex items-center mb-2">
                <img src="@img/Logo.png" alt="CamboMart Logo" class="w-12 h-12 mr-2" />
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 leading-tight">Cambo<span class="text-emerald-500">Mart</span></h1>
                    <p class="text-xs text-gray-500">Your Marketplace</p>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mt-4">Welcome Back</h2>
            <p class="text-sm text-gray-400">Sign in to continue to Cambo-Mart</p>
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="relative">
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-gray-200 rounded-xl px-4 py-3 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.email"
                    placeholder="Email address"
                    required
                    autofocus
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

            <!-- Eye Button -->
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
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.681 16.673A8.968 8.968 0 0112 18
                            c-4.478 0-8.268-2.943-9.542-7
                            a8.966 8.966 0 012.19-3.249"/>
                </svg>
            </button>

            <InputError class="mt-2" :message="form.errors.password" />
        </div>


            <div class="flex items-center justify-between mt-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="text-emerald-500 rounded" />
                    <span class="ms-2 text-sm text-gray-500">Remember me</span>
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-semibold text-emerald-500 hover:text-emerald-600"
                >
                    Forgot Password?
                </Link>
            </div>

            <div class="mt-6">
                <PrimaryButton 
                    class="w-full flex justify-center py-3 bg-green-500 hover:bg-emerald-600 rounded-xl transition duration-150 "
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing"
                >
                    Sign In
                </PrimaryButton>
            </div>

            <div class="mt-8">
                <div class="relative flex items-center justify-center mb-6">
                    <div class="border-t border-gray-200 w-full"></div>
                    <span class="absolute bg-white px-4 text-xs text-gray-400">New to Cambo-Mart?</span>
                </div>
                
                <Link
                    :href="route('register')"
                    class="w-full inline-flex justify-center items-center px-4 py-3 border border-emerald-500 text-emerald-500 font-semibold rounded-xl hover:bg-emerald-50 focus:outline-none transition duration-150"
                >
                    Create Account
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>