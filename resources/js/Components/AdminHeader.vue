<template>
  <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-white px-6">
    <div>
      <h1 class="text-xl font-semibold text-gray-900">{{ title }}</h1>
      <p v-if="subtitle" class="text-sm text-gray-500">{{ subtitle }}</p>
    </div>

    <div class="flex items-center gap-4">
      <!-- Search only on desktop -->
      <input
        v-if="!isMobile"
        type="search"
        placeholder="Search..."
        class="w-64 pl-2 h-10 border rounded-lg bg-gray-100 text-gray-900"
      />

      <!-- Notifications -->
      <button class="relative p-2 rounded-full hover:bg-gray-200">
        🔔
        <span class="absolute -right-1 -top-1 h-5 w-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">
          3
        </span>
      </button>

      <!-- User Dropdown -->
      <div class="relative">
        <button @click="open = !open" class="flex items-center gap-2 px-2 py-1 border rounded-lg hover:bg-gray-100">
          <div class="h-8 w-8 rounded-full bg-blue-600 text-white flex items-center justify-center">AD</div>
          <span class="hidden md:inline-block">Admin</span>
        </button>

        <div v-if="open" class="absolute right-0 mt-2 w-48 rounded-lg border bg-white shadow-lg">
          <p class="px-4 py-2 border-b text-gray-700 font-medium">My Account</p>
          <ul>
            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Profile</li>
            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Settings</li>
            <li class="px-4 py-2 text-red-500 hover:bg-gray-100 cursor-pointer">Logout</li>
          </ul>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from "vue";
import { useMobile } from "@/composables/useMobile.js";

const props = defineProps({
  title: { type: String, default: "Dashboard" },
  subtitle: { type: String, default: "" }
});

const isMobile = useMobile();
const open = ref(false);
</script>
