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
        <div class="min-w-[40px] h-10 bg-gradient-to-tr from-green-600 to-emerald-400 flex items-center justify-center rounded-xl shadow-lg shadow-green-500/20">
          <v-icon color="white" size="24">mdi-storefront</v-icon>
        </div>
        <div v-if="!collapsed" class="overflow-hidden whitespace-nowrap">
          <h1 class="font-black text-xl tracking-tight text-white">Cambo<span class="text-green-400">Mart</span></h1>
        </div>
      </div>
    </div>

    <nav class="flex-1 px-3 space-y-1">
      <div v-for="item in navItems" :key="item.path">
        
        <p v-if="!collapsed && item.isHeader" class="text-[11px] font-bold text-slate-500 px-3 mt-6 mb-2 uppercase tracking-wider">
          {{ item.title }}
        </p>

        <a
          v-else-if="!item.isHeader"
          :href="item.path"
          :class="[
            'flex items-center gap-4 px-3 py-3 rounded-lg transition-all duration-200 group relative',
            /* ACTIVE: If currentPath matches item path, use green tint */
            currentPath === item.path 
              ? 'bg-green-500/10 text-green-400 font-bold' 
              : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'
          ]"
        >
          <div 
            v-if="currentPath === item.path"
            class="absolute left-0 top-2 bottom-2 w-1 bg-green-500 rounded-r-full shadow-[0_0_8px_rgba(34,197,94,0.6)]"
          ></div>

          <v-icon 
            :color="currentPath === item.path ? 'green-accent-3' : ''"
            :class="[collapsed ? 'mx-auto' : '', 'transition-colors']"
          >
            {{ item.icon }}
          </v-icon>
          
          <span v-if="!collapsed" class="text-sm tracking-wide">
            {{ item.title }}
          </span>

          <div v-if="collapsed" class="absolute left-16 bg-slate-800 text-white text-[11px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity border border-slate-700 whitespace-nowrap z-50">
            {{ item.title }}
          </div>
        </a>
      </div>
    </nav>

    <div class="p-4 border-t border-slate-800">
      <button
        @click="$emit('toggle')"
        class="w-full flex items-center justify-center py-2.5 rounded-lg bg-slate-800/40 hover:bg-slate-800 text-slate-400 hover:text-white transition-all border border-slate-700/30"
      >
        <v-icon size="20">{{ collapsed ? 'mdi-chevron-right' : 'mdi-backburger' }}</v-icon>
        <span v-if="!collapsed" class="ml-2 text-sm font-semibold">Hide Panel</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
/**
 * currentPath must match the item.path exactly for the active state to trigger.
 * Example: If you are at /admin/users, the 'Users' item will light up green.
 */
const props = defineProps({
  collapsed: Boolean,
  currentPath: { type: String, default: '/' } 
});

const navItems = [
  { title: "Main Menu", isHeader: true },
  { title: "Dashboard", path: "/", icon: "mdi-view-dashboard-outline" },
  { title: "Users", path: "/admin/users", icon: "mdi-account-group-outline" },
  { title: "Products", path: "/products", icon: "mdi-package-variant-closed" },
  { title: "Orders", path: "/orders", icon: "mdi-tray-full" },
  { title: "System", isHeader: true },
  { title: "Settings", path: "/settings", icon: "mdi-cog-outline" },
];
</script>