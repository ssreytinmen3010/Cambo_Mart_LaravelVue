<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useActivePath } from '@/composables/useActivePath';
import { useStore } from '@/composables/useStore';

const mobileOpen = ref(false);
const dropdownOpen = ref(false);
const { cartCount, wishlist } = useStore();

const page = usePage();
const { isActive } = useActivePath();

const navLinks = [
    { href: route('home'), label: 'Home', match: '/' },
    { href: route('shop'), label: 'Shop', match: '/shop' },
    { href: route('categories'), label: 'Categories', match: '/categories' },
    { href: route('brand'), label: 'Brands', match: '/brand' },
    { href: route('about'), label: 'About', match: '/about' },
    { href: route('contact'), label: 'Contact', match: '/contact' },
];

function toggleDropdown() {
    dropdownOpen.value = !dropdownOpen.value;
}

function linkActive(match) {
    return isActive(match);
}
</script>

<template>
    <header class="sticky top-0 z-40 w-full bg-background/85 backdrop-blur-xl border-b border-border/60">
        <div class="bg-foreground text-background text-xs">
            <div class="container mx-auto px-4 py-2 flex items-center justify-between">
                <span class="opacity-80">🌿 Naturally sourced. Delivered fresh across Cambodia.</span>

                <div class="hidden md:flex items-center gap-4 opacity-80">
                    <Link :href="route('about')" class="hover:text-primary transition-colors">Help</Link>
                    <Link :href="route('contact')" class="hover:text-primary transition-colors">Track order</Link>
                    <span>EN · USD</span>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4">
            <div class="flex items-center gap-4 h-18 py-3">
                <Link :href="route('home')" class="flex items-center gap-2 group shrink-0">
                    <div class="h-10 w-10 rounded-2xl bg-gradient-brand grid place-items-center text-primary-foreground shadow-glow group-hover:scale-105 transition-transform">
                        ✨
                    </div>
                    <div class="leading-tight">
                        <div class="text-lg font-bold tracking-tight">
                            Cambo<span class="text-gradient-brand">Mart</span>
                        </div>
                        <div class="text-[10px] uppercase tracking-widest text-muted-foreground -mt-0.5">
                            Fresh · Natural
                        </div>
                    </div>
                </Link>

                <div class="flex-1 max-w-2xl hidden md:block">
                    <div class="relative group">
                        <input
                            type="search"
                            placeholder="Search for fresh produce, brands, categories…"
                            class="w-full h-11 pl-11 pr-28 rounded-full bg-secondary/70 border border-transparent focus:bg-background focus:border-primary/40 focus:ring-4 focus:ring-primary/10 outline-none text-sm transition-all"
                        />
                        <button
                            type="button"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 h-8 px-4 rounded-full bg-primary text-primary-foreground text-xs font-medium hover:bg-primary/90 transition-colors"
                        >
                            Search
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-1 ml-auto">
                    <Link
                        :href="route('wishlist')"
                        class="relative h-10 w-10 grid place-items-center rounded-full hover:bg-secondary transition-colors"
                    >
                        ❤️
                        <span
                            v-if="wishlist.length"
                            class="absolute -top-0.5 -right-0.5 h-5 min-w-5 px-1 grid place-items-center text-[10px] font-bold rounded-full bg-primary text-primary-foreground"
                        >
                            {{ wishlist.length }}
                        </span>
                    </Link>

                    <Link
                        :href="route('cart')"
                        class="relative h-10 w-10 grid place-items-center rounded-full hover:bg-secondary transition-colors"
                    >
                        🛒
                        <span
                            v-if="cartCount"
                            class="absolute -top-0.5 -right-0.5 h-5 min-w-5 px-1 grid place-items-center text-[10px] font-bold rounded-full bg-primary text-primary-foreground animate-scale-in"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <div class="relative hidden sm:block">
                        <button
                            type="button"
                            class="flex items-center gap-2 h-10 px-3 rounded-full hover:bg-secondary transition-colors text-sm font-medium"
                            @click="toggleDropdown"
                        >
                            <div class="h-7 w-7 rounded-full bg-gradient-brand grid place-items-center text-primary-foreground">👤</div>
                            ▼
                        </button>

                        <div
                            v-if="dropdownOpen"
                            class="absolute right-0 mt-2 w-56 bg-white shadow-lg rounded-xl border z-50"
                        >
                            <div class="px-4 py-3 font-semibold border-b">
                                {{ page.props.auth?.user ? `Hi, ${page.props.auth.user.name}` : 'Welcome to CamboMart' }}
                            </div>

                            <template v-if="!page.props.auth?.user">
                                <Link :href="route('login')" class="dropdown-item">Login</Link>
                                <Link :href="route('register')" class="dropdown-item">Register</Link>
                            </template>

                            <template v-else>
                                <Link :href="route('user.dashboard')" class="dropdown-item">My Dashboard</Link>
                                <Link :href="route('user.profile')" class="dropdown-item">My Profile</Link>
                                <Link :href="route('wishlist')" class="dropdown-item">Wishlist</Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="dropdown-item w-full text-left"
                                >
                                    Sign Out
                                </Link>
                            </template>
                        </div>
                    </div>

                    <div v-if="!page.props.auth?.user" class="hidden lg:flex items-center gap-2 ml-2">
                        <Link :href="route('login')">
                            <span class="inline-block px-4 py-2 rounded-lg border hover:bg-secondary">Login</span>
                        </Link>
                        <Link :href="route('register')">
                            <span class="inline-block px-4 py-2 rounded-lg bg-primary text-primary-foreground">Register</span>
                        </Link>
                    </div>

                    <button
                        type="button"
                        class="lg:hidden h-10 w-10 grid place-items-center rounded-full hover:bg-secondary"
                        @click="mobileOpen = !mobileOpen"
                    >
                        {{ mobileOpen ? '✖' : '☰' }}
                    </button>
                </div>
            </div>

            <div class="md:hidden pb-3">
                <input
                    type="search"
                    placeholder="Search CamboMart…"
                    class="w-full h-10 pl-4 pr-4 rounded-full bg-secondary/70 outline-none text-sm focus:bg-background focus:ring-4 focus:ring-primary/10"
                />
            </div>

            <nav class="hidden lg:flex items-center gap-1 h-12 border-t border-border/60">
                <Link
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="link.href"
                    class="relative px-4 h-12 grid place-items-center text-sm font-medium transition-colors"
                    :class="linkActive(link.match) ? 'text-primary' : 'text-foreground/80 hover:text-foreground'"
                >
                    {{ link.label }}
                    <span
                        v-if="linkActive(link.match)"
                        class="absolute bottom-0 left-3 right-3 h-0.5 bg-primary rounded-full"
                    />
                </Link>

                <div class="ml-auto flex items-center gap-2 text-xs text-muted-foreground">
                    <span class="h-2 w-2 rounded-full bg-primary animate-pulse" />
                    Delivering today across Phnom Penh
                </div>
            </nav>
        </div>

        <div v-if="mobileOpen" class="lg:hidden border-t border-border bg-background animate-fade-in">
            <nav class="container mx-auto px-4 py-3 flex flex-col">
                <Link
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="link.href"
                    class="py-3 border-b border-border/50 text-sm font-medium"
                    @click="mobileOpen = false"
                >
                    {{ link.label }}
                </Link>

                <div v-if="!page.props.auth?.user" class="flex gap-2 pt-3">
                    <Link :href="route('login')" class="flex-1" @click="mobileOpen = false">
                        <span class="block w-full text-center px-4 py-2 rounded-lg border">Login</span>
                    </Link>
                    <Link :href="route('register')" class="flex-1" @click="mobileOpen = false">
                        <span class="block w-full text-center px-4 py-2 rounded-lg bg-primary text-primary-foreground">Register</span>
                    </Link>
                </div>
            </nav>
        </div>
    </header>
</template>

<style scoped>
.dropdown-item {
    display: block;
    padding: 12px 16px;
    transition: 0.2s;
}

.dropdown-item:hover {
    background: #f5f5f5;
}
</style>
