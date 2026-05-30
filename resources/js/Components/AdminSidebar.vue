<template>
  <aside
    :class="[
      'fixed top-0 left-0 h-screen transition-all duration-300 ease-in-out z-50',
      'bg-slate-900 border-r border-slate-800 flex flex-col shadow-xl',
      collapsed ? 'w-20' : 'w-64'
    ]"
  >
    <div class="h-20 flex items-center px-5 mb-4">
      <div class="flex items-center gap-3">
        <div class="relative flex items-center justify-center w-12 h-12 bg-gradient-to-tr from-slate-100 to-white rounded-2xl shadow-xl border border-white">
          <img 
            :src="storeLogoUrl" 
            :alt="storeName + ' Logo'" 
            class="w-18 h-18 min-w-[72px] z-10 object-contain drop-shadow-xl transform hover:scale-110 transition-transform duration-300" 
            @error="onLogoError"
          />
          <div class="absolute inset-0 bg-white/40 blur-xl rounded-full"></div>
        </div>
        <div v-if="!collapsed" class="overflow-hidden whitespace-nowrap">
          <h1 class="font-black text-xl tracking-tight text-white">
            {{ storeNameParts.first }}<span class="text-green-400">{{ storeNameParts.second }}</span>
          </h1>
        </div>
      </div>
    </div>

    <nav class="flex-1 px-3 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-hide">
      <div v-for="item in navItems" :key="item.path || item.title">
        
        <p v-if="!collapsed && item.isHeader" class="text-[11px] font-bold text-slate-500 px-3 mt-6 mb-2 uppercase tracking-wider">
          {{ item.title }}
        </p>

        <div v-else-if="item.children" class="space-y-1">
          <button
            type="button"
            :class="[
              'w-full flex items-center gap-4 px-3 py-3 rounded-lg transition-all duration-200 group relative border-0 bg-transparent',
              isGroupActive(item)
                ? 'bg-green-500/10 font-bold'
                : 'hover:bg-slate-800'
            ]"
            @click="toggleGroup(item.title)"
          >
            <div
              v-if="isGroupActive(item)"
              class="absolute left-0 top-2 bottom-2 w-1 bg-green-500 rounded-r-full shadow-[0_0_8px_rgba(34,197,94,0.6)]"
            ></div>

            <v-icon :class="iconClasses(isGroupActive(item), collapsed)">
              {{ item.icon }}
            </v-icon>

            <span
              v-if="!collapsed"
              class="text-sm tracking-wide flex-1 text-left transition-colors"
              :class="isGroupActive(item) ? 'text-green-400' : 'text-slate-400 group-hover:text-slate-100'"
            >
              {{ item.title }}
            </span>

            <v-icon
              v-if="!collapsed"
              size="18"
              :class="isGroupActive(item) ? 'text-green-400' : 'text-slate-500 group-hover:text-slate-300'"
            >
              {{ isGroupExpanded(item.title) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}
            </v-icon>

            <div v-if="collapsed" class="absolute left-16 bg-slate-800 text-white text-[11px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity border border-slate-700 whitespace-nowrap z-50">
              {{ item.title }}
            </div>
          </button>

          <div v-if="isGroupExpanded(item.title) && !collapsed" class="ml-4 space-y-1 border-l border-slate-800 pl-2">
            <Link
              v-for="child in item.children"
              :key="child.path"
              :href="child.path"
              :class="[
                'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group relative',
                isActive(child.path)
                  ? 'bg-green-500/10 text-green-400 font-bold'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'
              ]"
            >
              <v-icon size="18" :class="iconClasses(isActive(child.path))">{{ child.icon }}</v-icon>
              <span
                class="text-sm tracking-wide transition-colors"
                :class="isActive(child.path) ? 'text-green-400' : 'text-slate-400 group-hover:text-slate-100'"
              >
                {{ child.title }}
              </span>
            </Link>
          </div>
        </div>

        <Link
          v-else-if="!item.isHeader"
          :href="item.path"
          :class="[
            'flex items-center gap-4 px-3 py-3 rounded-lg transition-all duration-200 group relative',
            isActive(item.path)
              ? 'bg-green-500/10 text-green-400 font-bold' 
              : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'
          ]"
        >
          <div 
            v-if="isActive(item.path)"
            class="absolute left-0 top-2 bottom-2 w-1 bg-green-500 rounded-r-full shadow-[0_0_8px_rgba(34,197,94,0.6)]"
          ></div>

          <v-icon :class="iconClasses(isActive(item.path), collapsed)">
            {{ item.icon }}
          </v-icon>
          
          <span v-if="!collapsed" class="text-sm tracking-wide">
            {{ item.title }}
          </span>

          <div v-if="collapsed" class="absolute left-16 bg-slate-800 text-white text-[11px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity border border-slate-700 whitespace-nowrap z-50">
            {{ item.title }}
          </div>
        </Link>
      </div>
    </nav>

    <div class="p-4 border-t border-slate-800">
      <button
        @click="$emit('toggle')"
        class="w-full flex items-center justify-center py-2.5 rounded-lg bg-slate-800/40 hover:bg-slate-800 text-slate-400 hover:text-white transition-all border border-slate-700/30"
      >
        <v-icon size="20" class="text-slate-400 group-hover:text-white transition-colors">{{ collapsed ? 'mdi-chevron-right' : 'mdi-backburger' }}</v-icon>
        <span v-if="!collapsed" class="ml-2 text-sm font-semibold">Hide Panel</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import defaultLogo from '@img/Logo.png';

const props = defineProps({
  collapsed: Boolean,
});

const page = usePage();
const settings = computed(() => page.props.appSettings || {});
const storeName = computed(() => settings.value.store_name || 'CamboMart');
const storeLogo = computed(() => settings.value.logo);

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

// Split store name for styling (e.g., "CamboMart" -> "Cambo" + "Mart")
const storeNameParts = computed(() => {
  const name = storeName.value;
  const mid = Math.ceil(name.length / 2);
  return {
    first: name.substring(0, mid),
    second: name.substring(mid)
  };
});

const navItems = [
  { title: "Main Menu", isHeader: true },
  { title: "Dashboard", path: "/admin/dashboard", icon: "mdi-view-dashboard-outline" },
  // { title: "Locations", path: "/admin/locations", icon: "mdi-map-marker-radius-outline" },
  { title: "Users", path: "/admin/users", icon: "mdi-account-group-outline" },
  { title: "Products", path: "/admin/products", icon: "mdi-package-variant-closed" },
  {
    title: "Promotions",
    icon: "mdi-percent",
    children: [
      { title: "Promotion", path: "/admin/promotions", icon: "mdi-tag-outline" },
      { title: "Promotion Season", path: "/admin/promotion-seasons", icon: "mdi-calendar-star" },
      { title: "Delivery", path: "/admin/deliveries", icon: "mdi-truck-delivery-outline" },
    ],
  },
  { title: "Orders", path: "/admin/orders", icon: "mdi-tray-full" },
  { title: "Reviews", path: "/admin/reviews", icon: "mdi-star-outline" },
  { title: "Contacts", path: "/admin/contacts", icon: "mdi-email-outline" },
  // { title: "Reports", path: "/admin/reports", icon: "mdi-chart-box-outline" },
  { title: "System", isHeader: true },
  { title: "Settings", path: "/admin/settings", icon: "mdi-cog-outline" },
];



const currentPath = computed(() => page.url);

const expandedGroups = ref({});

function isPromotionPath(path) {
  return path === '/admin/promotions'
    || path.startsWith('/admin/promotion-seasons')
    || path.startsWith('/admin/deliveries');
}

watch(
  currentPath,
  (path) => {
    if (isPromotionPath(path)) {
      expandedGroups.value.Promotions = true;
    }
  },
  { immediate: true }
);

function toggleGroup(title) {
  expandedGroups.value[title] = !expandedGroups.value[title];
}

function isGroupExpanded(title) {
  return !!expandedGroups.value[title];
}

function isGroupActive(item) {
  return item.children?.some((child) => isActive(child.path)) ?? false;
}

function iconClasses(active, collapsed = false) {
  return [
    collapsed ? 'mx-auto' : '',
    'transition-colors',
    active ? '!text-green-400' : '!text-slate-400 group-hover:!text-slate-100',
  ];
}

const isActive = (path) => {
  if (path === '/admin/products') {
    return currentPath.value === '/admin/products'
      || currentPath.value.startsWith('/admin/products')
      || currentPath.value.startsWith('/admin/brands')
      || currentPath.value.startsWith('/admin/categories');
  }

  if (path === '/admin/promotions') {
    return currentPath.value === '/admin/promotions';
  }

  return currentPath.value === path || currentPath.value.startsWith(`${path}/`);
};
</script>
