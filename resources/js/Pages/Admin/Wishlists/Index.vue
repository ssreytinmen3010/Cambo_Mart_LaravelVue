<script setup>
import { ref, computed, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router, Link } from "@inertiajs/vue3";

const props = defineProps({
  wishlists: Object,
  filters: Object
});

const search = ref(props.filters?.search || "");
const from_date = ref(props.filters?.from_date || "");
const to_date = ref(props.filters?.to_date || "");

const filteredWishlists = computed(() => props.wishlists.data);

watch([search, from_date, to_date], ([newSearch, newFrom, newTo]) => {
  router.get(route('admin.wishlists.index'), { 
    search: newSearch, from_date: newFrom, to_date: newTo 
  }, { preserveState: true, replace: true });
});

function deleteItem(id) {
  if(confirm("Remove this item from user's wishlist?")) router.delete(route('admin.wishlists.destroy', id));
}
</script>

<template>
  <AdminLayout title="Wishlists" subtitle="Track user interests">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-black text-slate-800 tracking-tight">User Wishlists</h1>
    </div>

    <!-- Filter Bar -->
    <div class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm backdrop-blur-xl">
      <div class="flex flex-col md:flex-row items-center gap-2">
        <div class="relative w-full md:w-1/3">
          <input v-model="search" type="text" placeholder="Search product or user..." class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-sm focus:ring-4 focus:ring-green-500/10 transition-all placeholder:text-slate-400 font-medium"/>
          <div class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center bg-green-500 rounded-lg shadow-sm">
            <v-icon color="white" size="14">mdi-magnify</v-icon>
          </div>
        </div>
        
        <div class="flex items-center gap-2 p-1 bg-slate-50/50 rounded-[1.5rem] w-full md:w-auto">
          <div class="flex items-center gap-2 px-3">
             <v-icon size="18" class="text-slate-400">mdi-calendar-range</v-icon>
          </div>
          <div class="flex items-center gap-1.5 bg-white p-1 rounded-2xl shadow-sm border border-slate-100">
            <input v-model="from_date" type="date" class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer"/>
            <div class="w-[1px] h-4 bg-slate-200"></div>
            <input v-model="to_date" type="date" class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer"/>
          </div>
           <button v-if="search || from_date || to_date" @click="search=''; from_date=''; to_date=''" class="ml-2 mr-2 w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 active:scale-90 transition-all"><v-icon size="16">mdi-filter-off</v-icon></button>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            <th class="px-6 py-3">Product</th>
            <th class="px-6 py-3">Price</th>
            <th class="px-6 py-3">User</th>
            <th class="px-6 py-3">Added Date</th>
            <th class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="w in filteredWishlists" :key="w.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
               <div class="flex items-center gap-3">
                 <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shrink-0">
                    <img v-if="w.product_image" :src="w.product_image" class="w-full h-full object-cover">
                    <v-icon v-else size="18" class="text-gray-300">mdi-package-variant</v-icon>
                 </div>
                 <div class="text-sm font-bold text-slate-700">{{ w.product_name }}</div>
               </div>
            </td>
            <td class="px-6 py-4 text-sm font-black text-green-600">${{ w.product_price }}</td>
            <td class="px-6 py-4">
               <div class="text-xs font-bold text-slate-700">{{ w.user_name }}</div>
               <div class="text-[10px] text-slate-400">{{ w.user_email }}</div>
            </td>
            <td class="px-6 py-4 text-[11px] text-slate-400 font-mono">{{ w.added_date }}</td>
            <td class="px-6 py-4 text-right">
              <button @click="deleteItem(w.id)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors"><v-icon size="18">mdi-delete</v-icon></button>
            </td>
          </tr>
        </tbody>
      </table>
       <div v-if="wishlists.links" class="p-3 flex justify-center gap-1">
         <Link v-for="(link, i) in wishlists.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'" />
      </div>
    </div>
  </AdminLayout>
</template>