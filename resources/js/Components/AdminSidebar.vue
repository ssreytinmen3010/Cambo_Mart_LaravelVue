<template>
  <aside :class="['fixed top-0 left-0 h-screen bg-gray-800 text-white transition-all duration-300', collapsed ? 'w-20' : 'w-64']">
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-700">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 flex items-center justify-center rounded">🏬</div>
        <span v-if="!collapsed" class="font-bold">Cambo_Mart</span>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4">
      <ul>
        <li v-for="item in navItems" :key="item.path">
          <a href="#" class="flex items-center gap-3 p-2 hover:bg-gray-700 rounded">
            <span>{{ item.icon }}</span>
            <span v-if="!collapsed">{{ item.title }}</span>
          </a>
        </li>
      </ul>
    </nav>

    <!-- Collapse Button -->
    <div class="p-3 border-t border-gray-700">
      <button @click="$emit('toggle')" class="w-full flex justify-center gap-2 p-2 hover:bg-gray-700 rounded">
        <span v-if="collapsed">➡️</span>
        <span v-else>⬅️ Collapse</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
const props = defineProps({
  collapsed: Boolean
})

const navItems = [
  { title: 'Dashboard', path: '/', icon: '📊' },
  { title: 'Users', path: '/users', icon: '👤' },
  { title: 'Products', path: '/products', icon: '📦' },
  { title: 'Orders', path: '/orders', icon: '🛒' },
  { title: 'Settings', path: '/settings', icon: '⚙️' },
];
</script>
