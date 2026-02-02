<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useToast } from "@/composables/useToast.js";

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      orders: 0,
      users: 0,
      products: 0,
      categories: 0,
      brands: 0
    })
  }
});

const { toasts, toast, dismiss } = useToast();

function showToast() {
  toast({ title: "Success!", description: "Welcome to Admin Dashboard" });
}
</script>

<template>
  <Head title="Dashboard" />

  <AdminLayout title="Dashboard" subtitle="Welcome to Admin dashboard!">
    <!-- Summary Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
      <!-- Orders Card (NEW) -->
      <div class="relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-emerald-500/5 transition-all group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
          <v-icon size="64" color="emerald">mdi-cart-outline</v-icon>
        </div>
        <div class="flex flex-col">
          <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-4">
            <v-icon>mdi-cart-outline</v-icon>
          </div>
          <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Orders</span>
          <h3 class="text-3xl font-black text-slate-800">{{ stats.orders }}</h3>
        </div>
        <Link :href="route('admin.orders.index')" class="mt-4 flex items-center text-[11px] font-bold text-emerald-600">
          <v-icon size="14" class="mr-1">mdi-arrow-right</v-icon> Manage orders
        </Link>
      </div>

      <!-- Users Card -->
      <div class="relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition-all group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
          <v-icon size="64" color="blue">mdi-account-group</v-icon>
        </div>
        <div class="flex flex-col">
          <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4">
            <v-icon>mdi-account-group</v-icon>
          </div>
          <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Users</span>
          <h3 class="text-3xl font-black text-slate-800">{{ stats.users }}</h3>
        </div>
        <div class="mt-4 flex items-center text-[11px] font-bold text-blue-600">
          <v-icon size="14" class="mr-1">mdi-arrow-right</v-icon> Manage users
        </div>
      </div>

      <!-- Products Card -->
      <div class="relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-green-500/5 transition-all group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
          <v-icon size="64" color="green">mdi-package-variant-closed</v-icon>
        </div>
        <div class="flex flex-col">
          <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mb-4">
            <v-icon>mdi-package-variant-closed</v-icon>
          </div>
          <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Products</span>
          <h3 class="text-3xl font-black text-slate-800">{{ stats.products }}</h3>
        </div>
        <div class="mt-4 flex items-center text-[11px] font-bold text-green-600">
          <v-icon size="14" class="mr-1">mdi-arrow-right</v-icon> View inventory
        </div>
      </div>

      <!-- Categories Card -->
      <div class="relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-purple-500/5 transition-all group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
          <v-icon size="64" color="purple">mdi-shape</v-icon>
        </div>
        <div class="flex flex-col">
          <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-4">
            <v-icon>mdi-shape</v-icon>
          </div>
          <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Categories</span>
          <h3 class="text-3xl font-black text-slate-800">{{ stats.categories }}</h3>
        </div>
        <div class="mt-4 flex items-center text-[11px] font-bold text-purple-600">
          <v-icon size="14" class="mr-1">mdi-arrow-right</v-icon> Organize items
        </div>
      </div>

      <!-- Brands Card -->
      <div class="relative overflow-hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-orange-500/5 transition-all group">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
          <v-icon size="64" color="orange">mdi-tag-multiple</v-icon>
        </div>
        <div class="flex flex-col">
          <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mb-4">
            <v-icon>mdi-tag-multiple</v-icon>
          </div>
          <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Top Brands</span>
          <h3 class="text-3xl font-black text-slate-800">{{ stats.brands }}</h3>
        </div>
        <div class="mt-4 flex items-center text-[11px] font-bold text-orange-600">
          <v-icon size="14" class="mr-1">mdi-arrow-right</v-icon> Brand management
        </div>
      </div>
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
