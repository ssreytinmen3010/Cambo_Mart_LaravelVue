<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head } from "@inertiajs/vue3";
import { useToast } from "@/composables/useToast.js";

const { toasts, toast, dismiss } = useToast();

function showToast() {
  toast({ title: "Success!", description: "Welcome to Admin Dashboard" });
}
</script>

<template>
  <Head title="Dashboard" />

  <AdminLayout title="Dashboard" subtitle="Welcome to Admin dashboard!">
    <!-- <button @click="showToast" class="px-4 py-2 mb-4 bg-blue-600 text-white rounded">Show Toast</button> -->

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
      <div class="h-32 p-4 border rounded-lg bg-white shadow">Card 1</div>
      <div class="h-32 p-4 border rounded-lg bg-white shadow">Card 2</div>
      <div class="h-32 p-4 border rounded-lg bg-white shadow">Card 3</div>
    </div>

    <!-- Toast container -->
    <div class="fixed top-5 right-5 space-y-2">
      <div v-for="t in toasts" :key="t.id" class="p-3 bg-green-500 text-white rounded shadow">
        <p class="font-bold">{{ t.title }}</p>
        <p>{{ t.description }}</p>
        <button @click="dismiss(t.id)" class="underline mt-1">Dismiss</button>
      </div>
    </div>
  </AdminLayout>
</template>
