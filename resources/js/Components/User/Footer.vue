<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
const page = usePage();

const storeName = computed(() => page.props.appSettings?.store_name ?? 'CamboMart');
const storeAddress = computed(() => page.props.appSettings?.address ?? 'Phnom Penh, Cambodia');
const storePhone = computed(() => page.props.appSettings?.phone ?? '+855 23 456 789');
const storeEmail = computed(() => page.props.appSettings?.email ?? 'hello@cambomart.com');

// const socials = [
//     { icon: '📘' },
//     { icon: '📸' },
//     { icon: '🐦' },
//     { icon: '▶️' },
// ];

const shopLinks = [
    { href: route('shop'), label: 'All products' },
    { href: route('categories'), label: 'Categories' },
    { href: route('brand'), label: 'Brands' },
    { href: route('shop'), label: "Today's deals" },
    { href: route('shop'), label: 'New arrivals' },
];

const companyLinks = [
    { href: route('about'), label: 'About us' },
    { href: route('contact'), label: 'Contact' },
    { label: 'Careers' },
    { label: 'Press' },
    { label: 'Sustainability' },
];
</script>

<template>
    <footer class="mt-20 bg-foreground text-background">
        <div class="container mx-auto px-4 py-16">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <Link :href="route('home')" class="flex items-center gap-2">
                        <div class="h-10 w-10 rounded-2xl bg-gradient-brand grid place-items-center shadow-glow">✨</div>
                        <span class="text-xl font-bold">{{ storeName }}</span>
                    </Link>

                    <p class="mt-4 text-sm text-background/70 max-w-sm leading-relaxed">
                        Cambodia's premium natural marketplace. We connect local farmers and artisans with families who care about fresh, honest food.
                    </p>

                    <div class="mt-6 flex items-center gap-3">
                        <a
                            v-for="(social, index) in socials"
                            :key="index"
                            href="#"
                            class="h-10 w-10 grid place-items-center rounded-full bg-background/10 hover:bg-primary hover:text-primary-foreground transition-colors"
                        >
                            {{ social.icon }}
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Shop</h4>
                    <ul class="space-y-2.5 text-sm text-background/70">
                        <li v-for="item in shopLinks" :key="item.label">
                            <Link :href="item.href" class="hover:text-primary transition-colors">{{ item.label }}</Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2.5 text-sm text-background/70">
                        <li v-for="item in companyLinks" :key="item.label">
                            <Link v-if="item.href" :href="item.href" class="hover:text-primary transition-colors">
                                {{ item.label }}
                            </Link>
                            <a v-else href="#" class="hover:text-primary transition-colors">{{ item.label }}</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Get in touch</h4>
                    <ul class="space-y-3 text-sm text-background/70">
                        <li class="flex items-start gap-2">📍 {{ storeAddress }}</li>
                        <li class="flex items-center gap-2">📞 {{ storePhone }}</li>
                        <li class="flex items-center gap-2">✉️ {{ storeEmail }}</li>
                    </ul>

                    <div class="mt-5">
                        <p class="text-xs text-background/60 mb-2">Subscribe for fresh deals</p>
                        <div class="flex">
                            <input
                                class="flex-1 h-10 px-3 rounded-l-full bg-background/10 outline-none text-sm placeholder:text-background/40 focus:bg-background/15"
                                placeholder="Your email"
                            />
                            <button
                                type="button"
                                class="h-10 px-4 rounded-r-full bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90"
                            >
                                Join
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-background/10 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-background/60">
                <p>© {{ currentYear }} {{ storeName }}. All rights reserved.</p>
                <div class="flex items-center gap-5">
                    <a href="#" class="hover:text-primary">Privacy</a>
                    <a href="#" class="hover:text-primary">Terms</a>
                    <a href="#" class="hover:text-primary">Cookies</a>
                </div>
            </div>
        </div>
    </footer>
</template>
