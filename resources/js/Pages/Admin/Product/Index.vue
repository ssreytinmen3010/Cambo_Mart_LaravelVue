<script setup>
import { ref, computed, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, router, Link } from "@inertiajs/vue3";

const props = defineProps({
  products: Object,
  categories: Array,
  brands: Array,
  filters: Object
});

// State
const search = ref(props.filters?.search || "");
const from_date = ref(props.filters?.from_date || "");
const to_date = ref(props.filters?.to_date || "");
const showModal = ref(false);
const isEdit = ref(false);
const fileInput = ref(null);
const previewImage = ref(null);

const form = useForm({
  id: null,
  name: "",
  product_code: "",
  price: "",
  stock: "",
  category_id: "",
  brand_id: "",
  description: "",
  image: "",
  status: true
});

const filteredProducts = computed(() => {
  return props.products.data;
});

// Watch for search/date changes to trigger server-side filtering
watch([search, from_date, to_date], ([newSearch, newFrom, newTo]) => {
  router.get(route('admin.products.index'), { 
    search: newSearch, 
    from_date: newFrom, 
    to_date: newTo 
  }, { 
    preserveState: true, 
    replace: true 
  });
});

// Actions
function openAddModal() {
  isEdit.value = false;
  form.reset();
  form.status = true;
  previewImage.value = null;
  showModal.value = true;
}

function openEditModal(prod) {
  isEdit.value = true;
  form.id = prod.id;
  form.name = prod.name;
  form.product_code = prod.code;
  form.price = prod.price;
  form.stock = prod.stock;
  // Ensure Controller returns IDs in the 'through' mapping if you want to edit! 
  // Currently your controller returns names 'category' and 'brand'. 
  // You MUST update your ProductController to return 'category_id' and 'brand_id'.
  form.category_id = prod.category_id || ""; 
  form.brand_id = prod.brand_id || "";
  
  form.image = prod.image;
  form.status = prod.is_active;
  previewImage.value = prod.image;
  showModal.value = true;
}

async function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;
  previewImage.value = URL.createObjectURL(file);
  const formData = new FormData();
  formData.append('image', file);
  try {
    const axios = (await import('axios')).default;
    const res = await axios.post('/upload-image', formData);
    form.image = res.data.url;
  } catch (error) { alert("Upload failed"); }
}

function save() {
  const routeName = isEdit.value ? 'admin.products.update' : 'admin.products.store';
  
  if (isEdit.value) {
    form.put(route(routeName, form.id), { 
      onSuccess: () => showModal.value = false 
    });
  } else {
    form.post(route(routeName), { 
      onSuccess: () => showModal.value = false 
    });
  }
}

function deleteItem(id) {
  if(confirm("Delete product?")) router.delete(route('admin.products.destroy', id));
}
</script>

<template>
  <AdminLayout title="Products" subtitle="Manage inventory">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-black text-slate-800 tracking-tight">Product Inventory</h1>
      <div class="flex items-center gap-2">
        <Link href="/admin/brands" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-lg shadow-sm hover:bg-slate-50 transition-all flex items-center gap-1.5">
          <v-icon size="14">mdi-tag-outline</v-icon> Add Brand
        </Link>
        <Link href="/admin/categories" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-lg shadow-sm hover:bg-slate-50 transition-all flex items-center gap-1.5">
          <v-icon size="14">mdi-shape-outline</v-icon> Add Category
        </Link>
        <button @click="openAddModal" class="px-3 py-1.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-green-500/20 transition-all flex items-center gap-1.5 active:scale-95">
          <v-icon color="white" size="14">mdi-plus</v-icon> New Product
        </button>
      </div>
    </div>

    <!-- Deep Filter Header -->
    <div class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm backdrop-blur-xl">
      <div class="flex flex-col md:flex-row items-center gap-2">
        <!-- Search Section -->
        <div class="relative w-full md:w-1/3">
          <input 
            v-model="search" 
            type="text" 
            placeholder="Search name, code, sku..." 
            class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-sm focus:ring-4 focus:ring-green-500/10 transition-all placeholder:text-slate-400 font-medium"
          />
          <div class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center bg-green-500 rounded-lg shadow-sm">
            <v-icon color="white" size="14">mdi-magnify</v-icon>
          </div>
        </div>

        <!-- Date Range section -->
        <div class="flex items-center gap-2 p-1 bg-slate-50/50 rounded-[1.5rem] w-full md:w-auto">
          <div class="flex items-center gap-2 px-3">
            <v-icon size="18" class="text-slate-400">mdi-calendar-range</v-icon>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Date Range</span>
          </div>
          
          <div class="flex items-center gap-1.5 bg-white p-1 rounded-2xl shadow-sm border border-slate-100">
            <input 
              v-model="from_date" 
              type="date" 
              class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer"
            />
            <div class="w-[1px] h-4 bg-slate-200"></div>
            <input 
              v-model="to_date" 
              type="date" 
              class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer"
            />
          </div>
          
          <!-- Clear Button -->
          <button 
            v-if="search || from_date || to_date" 
            @click="search=''; from_date=''; to_date=''" 
            class="ml-2 mr-2 w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 active:scale-90 transition-all"
            title="Reset Filters"
          >
            <v-icon size="16">mdi-filter-off</v-icon>
          </button>
        </div>

        <div class="flex-1"></div>

        <!-- Meta info -->
        <div class="hidden lg:flex items-center px-6 text-[11px] font-bold text-slate-400 uppercase tracking-widest gap-2">
           <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
           {{ products.total }} Products Found
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            <th class="px-6 py-3">Product</th>
            <th class="px-6 py-3">Code</th>
            <th class="px-6 py-3">Brand / Category</th>
            <th class="px-6 py-3">Price</th>
            <th class="px-6 py-3">Stock</th>
            <th class="px-6 py-3">Created</th>
            <th class="px-6 py-3">Updated</th>
            <th class="px-6 py-3 text-center">Status</th>
            <th class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="p in filteredProducts" :key="p.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                 <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100">
                  <img v-if="p.image" :src="p.image" class="w-full h-full object-cover">
                  <v-icon v-else size="18" class="text-gray-300">mdi-package-variant</v-icon>
                 </div>
                 <div class="text-sm font-bold text-slate-700">{{ p.name }}</div>
              </div>
            </td>
            <td class="px-6 py-4 text-xs font-mono text-slate-500">{{ p.code }}</td>
            <td class="px-6 py-4 text-xs">
              <div class="font-bold text-slate-600">{{ p.brand }}</div>
              <div class="text-slate-400">{{ p.category }}</div>
            </td>
            <td class="px-6 py-4 text-sm font-bold text-green-600">${{ p.price }}</td>
            <td class="px-6 py-4 text-sm font-bold" :class="p.stock > 0 ? 'text-slate-600' : 'text-red-500'">{{ p.stock }}</td>
            <td class="px-6 py-4 text-[11px] text-slate-500 font-medium">{{ p.created_at }}</td>
            <td class="px-6 py-4 text-[11px] text-slate-500 font-medium">{{ p.updated_at }}</td>
            <td class="px-6 py-4 text-center">
               <span :class="p.is_active ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">{{ p.status }}</span>
            </td>
            <td class="px-6 py-4 text-right">
               <div class="flex justify-end gap-2">
                <button @click="openEditModal(p)" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"><v-icon size="18">mdi-pencil</v-icon></button>
                <button @click="deleteItem(p.id)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg"><v-icon size="18">mdi-delete</v-icon></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
       <div v-if="products.links" class="p-3 flex justify-center gap-1">
         <Link v-for="(link, i) in products.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'" />
      </div>
    </div>

    <!-- MODAL -->
    <Transition name="modal-fade">
      <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showModal = false"></div>
        <div class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-pop flex flex-col max-h-[90vh]">
          <div class="bg-gradient-to-tr from-green-600 to-emerald-400 px-8 py-6 text-white shrink-0">
            <h2 class="text-xl font-black tracking-tight">{{ isEdit ? 'Edit Product' : 'New Product' }}</h2>
          </div>
          
          <div class="p-8 space-y-4 overflow-y-auto custom-scrollbar">
             <!-- Image -->
             <div class="flex justify-center mb-4">
                <div class="relative w-24 h-24 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden cursor-pointer" @click="$refs.fileInput.click()">
                  <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover">
                  <v-icon v-else class="text-slate-300">mdi-camera-plus</v-icon>
                </div>
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleImageUpload">
             </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Name</label>
                <input v-model="form.name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
              </div>
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Code</label>
                <input v-model="form.product_code" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
              </div>
            </div>

             <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Brand</label>
                <select v-model="form.brand_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none">
                  <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
              </div>
               <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Category</label>
                <select v-model="form.category_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none">
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
               <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Price</label>
                <input v-model="form.price" type="number" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
              </div>
               <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Stock</label>
                <input v-model="form.stock" type="number" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
              </div>
            </div>
             <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Status</label>
               <select v-model="form.status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
            <button @click="showModal=false" class="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600">Cancel</button>
            <button @click="save" :disabled="form.processing" class="px-8 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 shadow-lg active:scale-95 transition-all">Save</button>
          </div>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<style scoped>
/* Same animations */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.animate-pop { animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes pop { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>