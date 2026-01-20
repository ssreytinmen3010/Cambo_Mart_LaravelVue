<template>
  <v-app class="min-h-screen flex bg-gray-50">
    <!-- Sidebar -->
    <AdminSidebar
      :collapsed="sidebarCollapsed"
      @toggle="sidebarCollapsed = !sidebarCollapsed"
    />

    <!-- Main Content -->
    <div
      :class="[
        'transition-all duration-300 flex-1 min-h-screen',
        sidebarCollapsed ? 'ml-20' : 'ml-64'
      ]"
    >
      <!-- Header -->
      <AdminHeader :title="title" :subtitle="subtitle" />

      <!-- Page Content -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </v-app>
</template>

<script setup>
import { ref } from "vue";

// Components
import AdminHeader from "@/Components/AdminHeader.vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

// Props
const props = defineProps({
  title: { type: String, default: "Dashboard" },
  subtitle: { type: String, default: "" }
});

// Sidebar collapse state
const sidebarCollapsed = ref(false);
</script>

<style scoped>
/* Optional global layout styles */
</style>
