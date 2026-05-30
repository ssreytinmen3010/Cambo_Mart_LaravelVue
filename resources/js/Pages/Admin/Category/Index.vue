<script setup>
import { ref, computed } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, router } from "@inertiajs/vue3";

const props = defineProps({
  categories: Object,
  brands: Array // Ensure Controller passes this for the dropdown!
});

// State
const searchName = ref("");
const filterBrand = ref(""); // New filter for category list
const showModal = ref(false);
const isEdit = ref(false);
const fileInput = ref(null);
const previewImage = ref(null);

const form = useForm({
  id: null,
  name: "",
  brand_ids: [],
  description: "",
  image: "",
  status: true
});

const filteredCategories = computed(() => {
  let data = props.categories.data;
  if (searchName.value) {
    data = data.filter(c => c.name.toLowerCase().includes(searchName.value.toLowerCase()));
  }
  // You might need to adjust based on how your controller returns brand data
  // Assuming 'brand' string in table, but we might not have ID easily accessible in flattened table data. 
  // Ideally, do this server side.
  return data; 
});

// Actions
function openAddModal() {
  isEdit.value = false;
  form.reset();
  form.status = true;
  form.brand_ids = [];
  previewImage.value = null;
  showModal.value = true;
}

function openEditModal(cat) {
  isEdit.value = true;
  form.id = cat.id;
  form.name = cat.name;
  form.brand_ids = Array.isArray(cat.brand_ids) ? cat.brand_ids.map((id) => Number(id)) : (cat.brand_id ? [Number(cat.brand_id)] : []);
  form.description = cat.description;
  form.image = cat.image;
  form.status = cat.is_active;
  previewImage.value = cat.image;
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
  const routeName = isEdit.value ? 'admin.categories.update' : 'admin.categories.store';
  
  if (isEdit.value) {
      form.put(route(routeName, form.id), { onSuccess: () => showModal.value = false });
  } else {
      form.post(route(routeName), { onSuccess: () => showModal.value = false });
  }
}

function deleteItem(id) {
  if(confirm("Delete category?")) router.delete(route('admin.categories.destroy', id));
}
</script>

<template>
  <AdminLayout title="Categories" subtitle="Organize your items">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-black text-slate-800 tracking-tight">Category List</h1>
      <button @click="openAddModal" class="px-3 py-1.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-green-500/20 transition-all flex items-center gap-1.5 active:scale-95">
        <v-icon color="white" size="14">mdi-plus</v-icon> New Category
      </button>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4 bg-white p-2 rounded-3xl border-slate-100 shadow-sm w-full md:w-1/2">
       <div class="relative">
        <input v-model="searchName" type="text" placeholder="Search..." class="w-full pl-4 pr-4 py-1.5 bg-slate-50 border border-slate-100 rounded-full text-xs focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all"/>
      </div>
       <div class="relative">
         <!-- This is just visual unless you implement server filtering or have all data -->
        <select class="w-full pl-4 pr-8 py-1.5 bg-slate-50 border border-slate-100 rounded-full text-xs font-medium text-slate-600 outline-none appearance-none">
          <option value="">All Brands</option>
          <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            <th class="px-6 py-3">Category</th>
            <th class="px-6 py-3">Parent Brand</th>
            <th class="px-6 py-3">Total Product</th>
            <th class="px-6 py-3">Created</th>
            <th class="px-6 py-3">Updated</th>
            <th class="px-6 py-3 text-center">Status</th>
            <th class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="cat in filteredCategories" :key="cat.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center overflow-hidden">
                   <img v-if="cat.image" :src="cat.image" class="w-full h-full object-cover">
                   <v-icon v-else size="18">mdi-shape</v-icon>
                </div>
                <div class="text-sm font-bold text-slate-700">{{ cat.name }}</div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm font-medium text-slate-600">{{ cat.brand }}</td>
            <td class="px-6 py-4 text-sm font-semibold text-slate-700">{{ cat.products_count ?? 0 }}</td>
            <td class="px-6 py-4 text-[11px] text-slate-500 font-medium">{{ cat.created_at }}</td>
            <td class="px-6 py-4 text-[11px] text-slate-500 font-medium">{{ cat.updated_at }}</td>
            <td class="px-6 py-4 text-center">
               <span :class="cat.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">{{ cat.status }}</span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button @click="openEditModal(cat)" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"><v-icon size="18">mdi-pencil</v-icon></button>
                <button @click="deleteItem(cat.id)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg"><v-icon size="18">mdi-delete</v-icon></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="categories.links" class="p-3 flex justify-center gap-1">
         <Link v-for="(link, i) in categories.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'" />
      </div>
    </div>

    <!-- MODAL -->
    <Transition name="modal-fade">
      <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showModal = false"></div>
        <div class="relative bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden animate-pop">
          <div class="bg-white px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <div class="text-left">
              <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ isEdit ? 'Edit Category' : 'New Category' }}</h2>
            </div>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
              <v-icon size="24">mdi-close</v-icon>
            </button>
          </div>
          
          <div class="p-8 space-y-4">
            <!-- Icon Upload -->
             <div class="flex justify-center mb-2">
                <div class="relative w-16 h-16 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden cursor-pointer" @click="$refs.fileInput.click()">
                  <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover">
                  <v-icon v-else class="text-slate-300">mdi-image-plus</v-icon>
                </div>
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleImageUpload">
             </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Name</label>
                <input v-model="form.name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
              </div>
              <div class="space-y-2 md:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Brands</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                  <label v-for="b in brands" :key="b.id" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm transition hover:border-emerald-300">
                    <input v-model="form.brand_ids" type="checkbox" :value="b.id" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="font-medium">{{ b.name }}</span>
                  </label>
                </div>
                <p class="text-[11px] text-slate-500 ml-1">Select one or more brands for this category.</p>
              </div>
            </div>

            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Description</label>
              <textarea v-model="form.description" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none resize-none"></textarea>
            </div>
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Status</label>
               <select v-model="form.status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none cursor-pointer text-sm">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>
          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button @click="showModal=false" class="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">Cancel</button>
            <button @click="save" :disabled="form.processing" class="px-8 py-2.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white font-bold rounded-xl shadow-lg shadow-green-500/20 active:scale-95 transition-all">
              Save changes
            </button>
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
