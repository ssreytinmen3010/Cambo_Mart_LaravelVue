<template>
  <v-app class="min-h-screen flex bg-gray-50">
    <AdminSidebar :collapsed="sidebarCollapsed" @toggle="sidebarCollapsed = !sidebarCollapsed" />

    <div :class="['transition-all duration-300 flex-1', sidebarCollapsed ? 'ml-20' : 'ml-64']">
      <AdminHeader :title="title" :subtitle="subtitle" />
      <main class="p-6">
        <slot />
      </main>
    </div>
  </v-app>
</template>

<script setup>
import { ref } from "vue";
import AdminHeader from "@/Components/AdminHeader.vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({
  title: { type: String, default: "Dashboard" },
  subtitle: { type: String, default: "" }
});

const sidebarCollapsed = ref(false);
</script>
