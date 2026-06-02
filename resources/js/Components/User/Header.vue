<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useActivePath } from '@/composables/useActivePath';
import { useStore } from '@/composables/useStore';
import defaultLogo from '@img/Logo.png';

const mobileOpen = ref(false);
const dropdownOpen = ref(false);
const accountMenuRef = ref(null);
const { cartCount, wishlist, ensureCartLoaded, ensureWishlistLoaded, setCurrentUser } = useStore();

const page = usePage();
const { isActive } = useActivePath();

onMounted(() => {
    setCurrentUser(page.props.auth?.user?.id ?? null);
    if (page.props.auth?.user) {
        ensureCartLoaded();
        ensureWishlistLoaded();
    }
    document.addEventListener('click', onDocumentClick);
});

watch(
    () => page.props.auth?.user?.id ?? null,
    (newUserId) => {
        setCurrentUser(newUserId);
        if (newUserId) {
            ensureCartLoaded();
            ensureWishlistLoaded();
        }
    },
);

if (import.meta.env.DEV) {
    // eslint-disable-next-line no-console
    console.debug('Header appSettings:', page.props.appSettings);
}

const settings = computed(() => page.props.appSettings || {});
const storeName = computed(() => settings.value.store_name || 'CamboMart');
const storeLogo = computed(() => settings.value.logo || null);

function normalizeLogoUrl(logo) {
    if (!logo || typeof logo !== 'string') return null;
    const value = logo.trim().replaceAll('\\', '/');
    if (!value) return null;

    if (
        value.startsWith('http://') ||
        value.startsWith('https://') ||
        value.startsWith('data:') ||
        value.startsWith('blob:')
    ) {
        return value;
    }

    if (value.startsWith('/')) return value;

    if (value.startsWith('uploads/')) return `/storage/${value}`;
    if (value.startsWith('public/uploads/')) return `/storage/${value.replace(/^public\//, '')}`;
    if (value.startsWith('storage/')) return `/${value}`;

    return `/${value}`;
}

const storeLogoUrl = computed(() => normalizeLogoUrl(storeLogo.value) || defaultLogo);

function onLogoError(event) {
    if (event?.target) event.target.src = defaultLogo;
}

const storeNameParts = computed(() => {
    const name = String(storeName.value || '').trim();
    if (!name) return { prefix: 'Cambo', highlight: 'Mart' };

    const highlight = 'Mart';
    if (name.toLowerCase().endsWith(highlight.toLowerCase()) && name.length > highlight.length) {
        return { prefix: name.slice(0, -highlight.length), highlight: name.slice(-highlight.length) };
    }

    return { prefix: name, highlight: '' };
});

const navLinks = [
    { href: route('home'), label: 'Home', match: '/' },
    { href: route('shop'), label: 'Shop', match: '/shop' },
    { href: route('categories'), label: 'Categories', match: '/categories' },
    { href: route('brand'), label: 'Brands', match: '/brand' },
    { href: route('about'), label: 'About', match: '/about' },
    { href: route('contact'), label: 'Contact', match: '/contact' },
];

const authUser = computed(() => page.props.auth?.user ?? null);

const userInitials = computed(() => {
    const name = String(authUser.value?.name ?? '').trim();
    if (!name) return '?';
    const parts = name.split(/\s+/).filter(Boolean);
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
});

const avatarLoadFailed = ref(false);

watch(
    () => authUser.value?.image,
    () => {
        avatarLoadFailed.value = false;
    },
);

const userAvatarUrl = computed(() => {
    const image = authUser.value?.image;
    if (!image || image === 'null') return null;
    return normalizeLogoUrl(String(image));
});

const showUserAvatar = computed(() => Boolean(userAvatarUrl.value) && !avatarLoadFailed.value);

function onAvatarError() {
    avatarLoadFailed.value = true;
}

function toggleDropdown(event) {
    event?.stopPropagation();
    dropdownOpen.value = !dropdownOpen.value;
}

function closeDropdown() {
    dropdownOpen.value = false;
}

function onDocumentClick(event) {
    if (!dropdownOpen.value || !accountMenuRef.value) return;
    if (!accountMenuRef.value.contains(event.target)) {
        dropdownOpen.value = false;
    }
}

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
});

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
                    <div class="h-12 w-12 rounded-2xl bg-background  grid place-items-center  group-hover:scale-105 transition-transform overflow-hidden">
                        <img
                            :src="storeLogoUrl"
                            :alt="storeName"
                            class="h-full w-full object-contain p-0"
                            loading="lazy"
                            @error="onLogoError"
                        />
                    </div>
                    <div class="leading-tight">
                        <div class="text-lg font-bold tracking-tight">
                            {{ storeNameParts.prefix }}<span v-if="storeNameParts.highlight" class="text-gradient-brand">{{ storeNameParts.highlight }}</span>
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
                        class="relative h-10 w-10 grid place-items-center rounded-full hover:bg-secondary transition-colors text-foreground"
                        aria-label="Wishlist"
                    >
                        <v-icon size="22">mdi-heart-outline</v-icon>
                        <span
                            v-if="wishlist.length"
                            class="absolute -top-0.5 -right-0.5 h-5 min-w-5 px-1 grid place-items-center text-[10px] font-bold rounded-full bg-primary text-primary-foreground"
                        >
                            {{ wishlist.length }}
                        </span>
                    </Link>

                    <Link
                        :href="route('cart')"
                        class="relative h-10 w-10 grid place-items-center rounded-full hover:bg-secondary transition-colors text-foreground"
                        aria-label="Cart"
                    >
                        <v-icon size="22">mdi-cart-outline</v-icon>
                        <span
                            v-if="cartCount"
                            class="absolute -top-0.5 -right-0.5 h-5 min-w-5 px-1 grid place-items-center text-[10px] font-bold rounded-full bg-primary text-primary-foreground animate-scale-in"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <div ref="accountMenuRef" class="relative hidden sm:block">
                        <button
                            type="button"
                            class="flex items-center gap-2 h-10 pl-1.5 pr-2.5 rounded-full border transition-all text-sm font-medium"
                            :class="dropdownOpen
                                ? 'bg-secondary border-primary/30 ring-2 ring-primary/10'
                                : 'border-transparent hover:bg-secondary hover:border-border/60'"
                            aria-haspopup="menu"
                            :aria-expanded="dropdownOpen"
                            @click="toggleDropdown"
                        >
                            <div
                                class="h-7 w-7 rounded-full shrink-0 overflow-hidden grid place-items-center border border-border/60"
                                :class="showUserAvatar
                                    ? 'bg-background'
                                    : authUser
                                        ? 'bg-gradient-brand text-primary-foreground text-[11px] font-bold'
                                        : 'bg-secondary text-muted-foreground'"
                            >
                                <img
                                    v-if="showUserAvatar"
                                    :src="userAvatarUrl"
                                    :alt="authUser?.name ?? 'Profile'"
                                    class="h-full w-full object-cover"
                                    @error="onAvatarError"
                                />
                                <span v-else-if="authUser">{{ userInitials }}</span>
                                <v-icon v-else size="18">mdi-account-outline</v-icon>
                            </div>
                            <span v-if="authUser" class="hidden md:inline max-w-[88px] truncate text-foreground/90">
                                {{ authUser.name?.split(' ')[0] }}
                            </span>
                            <v-icon size="16" class="text-muted-foreground shrink-0">
                                {{ dropdownOpen ? 'mdi-chevron-up' : 'mdi-chevron-down' }}
                            </v-icon>
                        </button>

                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 translate-y-1 scale-95"
                            enter-to-class="opacity-100 translate-y-0 scale-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100 translate-y-0 scale-100"
                            leave-to-class="opacity-0 translate-y-1 scale-95"
                        >
                           <div
    v-if="dropdownOpen"
    class="account-menu absolute right-0 mt-2 w-56 overflow-hidden rounded-lg border border-border/80 bg-background/95 backdrop-blur-md z-50 shadow-lg"
    role="menu"
    @click.stop
>
    <div class="p-1.5 space-y-0.5">
        <template v-if="!authUser">
            <!-- Minimal Label -->
            <p class="px-2.5 py-1 text-[10px] uppercase tracking-wider text-muted-foreground/90 font-semibold">
                Account
            </p>
            
            <Link
                :href="route('login')"
                class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-accent transition-colors"
                @click="closeDropdown"
            >
                <v-icon size="16" class="text-primary">mdi-login</v-icon>
                <span class="text-xs font-medium">Sign in</span>
            </Link>
            
            <Link
                :href="route('register')"
                class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-accent transition-colors"
                @click="closeDropdown"
            >
                <v-icon size="16" class="text-foreground/70">mdi-account-plus-outline</v-icon>
                <span class="text-xs font-medium">Register</span>
            </Link>
            
            <div class="pt-1 px-1">
                <Link :href="route('register')" @click="closeDropdown">
                    <span class="flex w-full items-center justify-center gap-1 rounded bg-primary px-2 py-1.5 text-[11px] font-bold text-primary-foreground hover:bg-primary/90 transition-colors">
                        Join Free
                        <v-icon size="14">mdi-arrow-right</v-icon>
                    </span>
                </Link>
            </div>
        </template>

        <template v-else>
            <!-- Minimal User Info -->
            <div class="px-2.5 py-2 mb-1 bg-secondary/30 rounded-md">
                <p class="text-xs font-bold truncate">Hi, {{ authUser.name.split(' ')[0] }}</p>
                <p class="text-[10px] text-muted-foreground truncate">{{ authUser.email }}</p>
            </div>
            
            <Link
                :href="route('user.profile')"
                class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-accent transition-colors"
                @click="closeDropdown"
            >
                <v-icon size="16" class="text-foreground/70">mdi-account-circle-outline</v-icon>
                <span class="text-xs font-medium">Profile</span>
            </Link>
            
            <Link
                :href="route('wishlist')"
                class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-accent transition-colors"
                @click="closeDropdown"
            >
                <v-icon size="16" class="text-rose-500">mdi-heart-outline</v-icon>
                <span class="text-xs font-medium flex-1">Wishlist</span>
                <span v-if="wishlist.length" class="text-[10px] font-bold opacity-60">{{ wishlist.length }}</span>
            </Link>
            
            <Link
                :href="route('cart')"
                class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-accent transition-colors"
                @click="closeDropdown"
            >
                <v-icon size="16" class="text-amber-600">mdi-cart-outline</v-icon>
                <span class="text-xs font-medium flex-1">Cart</span>
                <span v-if="cartCount" class="text-[10px] font-bold opacity-60">{{ cartCount }}</span>
            </Link>

            <div class="my-1 border-t border-border/40" />

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 hover:bg-destructive/10 text-destructive transition-colors"
                @click="closeDropdown"
            >
                <v-icon size="16">mdi-logout</v-icon>
                <span class="text-xs font-medium">Sign out</span>
            </Link>
        </template>
    </div>
</div>
                        </Transition>
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
                        <v-icon size="22">{{ mobileOpen ? 'mdi-close' : 'mdi-menu' }}</v-icon>
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
.account-menu {
    box-shadow: var(--shadow-card), var(--shadow-hover);
}

.account-menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.625rem 0.5rem;
    border-radius: 0.75rem;
    text-align: left;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.account-menu-item:hover {
    background: hsl(var(--secondary));
}

.account-menu-item--danger:hover {
    background: hsl(var(--destructive) / 0.08);
    color: hsl(var(--destructive));
}

.account-menu-icon {
    display: grid;
    place-items: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.625rem;
    flex-shrink: 0;
}
</style>
