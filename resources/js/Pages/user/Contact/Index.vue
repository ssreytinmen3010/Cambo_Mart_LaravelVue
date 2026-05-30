<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Mail, Phone, MapPin, MessageCircle } from 'lucide-vue-next';
import UserLayout from '@/Layouts/UserLayout.vue';
import UserBreadcrumb from '@/Components/User/UserBreadcrumb.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const submitted = ref(false);
const contactForm = useForm({
    full_name: authUser.value?.name ?? '',
    email: authUser.value?.email ?? '',
    subject: '',
    message: '',
});

const contacts = [
    { icon: MapPin, title: 'Visit us', value: 'Russian Blvd, Phnom Penh, Cambodia' },
    { icon: Phone, title: 'Call us', value: '+855 23 456 789' },
    { icon: Mail, title: 'Email us', value: 'hello@cambomart.com' },
    { icon: MessageCircle, title: 'Live chat', value: 'Available 8am – 9pm, daily' },
];

function handleSubmit() {
    contactForm.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            submitted.value = true;
            contactForm.reset('subject', 'message');
            contactForm.full_name = authUser.value?.name ?? '';
            contactForm.email = authUser.value?.email ?? '';
            setTimeout(() => {
                submitted.value = false;
            }, 4000);
        },
    });
}
</script>

<template>
    <Head title="Contact" />

    <UserLayout>
        <div class="container mx-auto px-4 py-12">
            <UserBreadcrumb :items="[{ label: 'Home', href: route('home') }, { label: 'Contact' }]" />

            <div class="mt-4 max-w-2xl">
                <span class="text-xs font-semibold uppercase tracking-wider text-primary">Contact us</span>
                <h1 class="mt-3 text-4xl md:text-5xl font-bold">
                    We'd love to hear <span class="text-gradient-brand">from you</span>
                </h1>
                <p class="mt-3 text-muted-foreground">Questions, feedback, partnerships — our team replies within 24 hours.</p>
            </div>

            <div v-if="submitted" class="mt-6 rounded-2xl bg-primary/10 text-primary px-4 py-3 text-sm font-medium">
                Message sent! We'll reply soon.
            </div>

            <div class="mt-10 grid lg:grid-cols-[1fr_360px] gap-8">
                <form
                    class="rounded-3xl bg-card border border-border/60 shadow-soft p-6 md:p-8 space-y-4"
                    @submit.prevent="handleSubmit"
                >
                    <div class="grid sm:grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Full name</span>
                            <input
                                v-model="contactForm.full_name"
                                required
                                type="text"
                                class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10"
                                placeholder="Enter your full name"
                            />
                        </label>
                        <label class="block">
                            <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Email</span>
                            <input
                                v-model="contactForm.email"
                                required
                                type="email"
                                class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10"
                                placeholder="Enter your email"
                            />
                        </label>
                    </div>
                    <label class="block">
                        <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Subject</span>
                        <input
                            v-model="contactForm.subject"
                            type="text"
                            class="w-full h-11 rounded-xl border bg-background px-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10"
                            placeholder="How can we help?"
                        />
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium text-muted-foreground mb-1.5 block">Message</span>
                        <textarea
                            v-model="contactForm.message"
                            required
                            rows="6"
                            class="w-full rounded-xl border bg-background p-4 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 resize-none"
                            placeholder="Write your message here..."
                        />
                    </label>
                    <button
                        type="submit"
                        class="rounded-full bg-primary px-7 py-3 text-primary-foreground font-medium hover:bg-primary/90 disabled:opacity-60"
                        :disabled="contactForm.processing"
                    >
                        Send message
                    </button>
                </form>

                <aside class="space-y-3">
                    <div
                        v-for="c in contacts"
                        :key="c.title"
                        class="flex gap-4 rounded-2xl bg-card border border-border/60 p-5 shadow-soft"
                    >
                        <div class="h-11 w-11 rounded-2xl bg-primary/10 text-primary grid place-items-center shrink-0">
                            <component :is="c.icon" class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="font-semibold text-sm">{{ c.title }}</p>
                            <p class="text-sm text-muted-foreground mt-0.5">{{ c.value }}</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </UserLayout>
</template>
