<script setup>
import { ref, computed } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, router } from "@inertiajs/vue3"; // Using Inertia form for easier file upload handling

const props = defineProps({
  brands: Object, // Paginator object from controller
});

// State
const searchName = ref("");
const showModal = ref(false);
const isEdit = ref(false);
const fileInput = ref(null);
const previewImage = ref(null);

// Form for Create/Edit
const form = useForm({
  id: null,
  name: "",
  image: "", // We will store the URL here
  status: true
});

// Computed Filter (Client-side search on current page, or server side if you prefer)
// For paginated data, it's better to trigger server search. 
// I will implement client-side filtering for the visual demo, 
// but realistically you should watch searchName and reload Inertia.
const filteredBrands = computed(() => {
  if (!searchName.value) return props.brands.data;
  return props.brands.data.filter(b => 
    b.name.toLowerCase().includes(searchName.value.toLowerCase())
  );
});

// Actions
function openAddModal() {
  isEdit.value = false;
  form.reset();
  form.status = true; // Default active
  previewImage.value = null;
  showModal.value = true;
}

function openEditModal(brand) {
  isEdit.value = true;
  form.id = brand.id;
  form.name = brand.name;
  form.image = brand.image;
  form.status = brand.is_active; // Controller sends is_active boolean
  previewImage.value = brand.image;
  showModal.value = true;
}

async function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  // Preview
  previewImage.value = URL.createObjectURL(file);

  // Upload to Cloudinary via your helper endpoint
  const formData = new FormData();
  formData.append('image', file);

  try {
    const axios = (await import('axios')).default; // Dynamic import if needed or standard import
    const res = await axios.post('/upload-image', formData);
    form.image = res.data.url;
  } catch (error) {
    alert("Image upload failed");
  }
}

function save() {
  if (isEdit.value) {
    form.put(route('admin.brands.update', form.id), {
      onSuccess: () => showModal.value = false
    });
  } else {
    form.post(route('admin.brands.store'), {
      onSuccess: () => showModal.value = false
    });
  }
}

function deleteItem(id) {
  if(confirm("Are you sure?")) {
    router.delete(route('admin.brands.destroy', id));
  }
}

// Simple pagination handler
function goToPage(url) {
  if(url) router.get(url);
}
</script>

<template>
  <AdminLayout title="Brands" subtitle="Manage product brands">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-black text-slate-800 tracking-tight">Brands List</h1>
      <button @click="openAddModal" class="px-3 py-1.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-green-500/20 transition-all flex items-center gap-1.5 active:scale-95">
        <v-icon color="white" size="14">mdi-plus</v-icon> New Brand
      </button>
    </div>

    <!-- Search -->
    <div class="grid grid-cols-1 mb-4 bg-white p-2 rounded-3xl border-slate-100 shadow-sm w-full md:w-1/3">
      <div class="relative">
        <input v-model="searchName" type="text" placeholder="Search brands..." class="w-full pl-4 pr-4 py-1.5 bg-slate-50 border border-slate-100 rounded-full text-xs focus:ring-4 focus:ring-green-500/10 focus:border-green-400 focus:bg-white outline-none transition-all placeholder:text-slate-400"/>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            <th class="px-6 py-3">Brand Info</th>
            <th class="px-6 py-3">Created</th>
            <th class="px-6 py-3">Updated</th>
            <th class="px-6 py-3 text-center">Status</th>
            <th class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="brand in filteredBrands" :key="brand.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                  <img v-if="brand.image" :src="brand.image" class="w-full h-full object-contain">
                  <span v-else class="text-xs font-bold text-gray-400">{{ brand.name.charAt(0) }}</span>
                </div>
                <div class="text-sm font-bold text-slate-700">{{ brand.name }}</div>
              </div>
            </td>
            <td class="px-6 py-4 text-[11px] text-slate-500 font-medium">{{ brand.created_at }}</td>
            <td class="px-6 py-4 text-[11px] text-slate-500 font-medium">{{ brand.updated_at }}</td>
            <td class="px-6 py-4 text-center">
              <span :class="brand.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                {{ brand.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button @click="openEditModal(brand)" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                  <v-icon size="18">mdi-pencil</v-icon>
                </button>
                <button @click="deleteItem(brand.id)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                  <v-icon size="18">mdi-delete</v-icon>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination Controls -->
      <div v-if="brands.links" class="p-3 border-t border-slate-100 flex justify-center gap-1">
         <!-- Simple buttons for prev/next based on your paginator structure -->
         <button 
            v-for="(link, i) in brands.links" :key="i"
            @click="goToPage(link.url)"
            v-html="link.label"
            class="px-3 py-1 text-xs rounded"
            :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-gray-100'"
            :disabled="!link.url"
         ></button>
      </div>
    </div>

    <!-- MODAL -->
    <Transition name="modal-fade">
      <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showModal = false"></div>

        <div class="relative bg-white w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-pop">
          <div class="bg-gradient-to-tr from-green-600 to-emerald-400 px-8 py-6 text-white">
            <h2 class="text-xl font-black tracking-tight">{{ isEdit ? 'Edit Brand' : 'Add New Brand' }}</h2>
          </div>

          <div class="p-8 space-y-4">
            <!-- Image Upload -->
             <div class="flex justify-center mb-4">
                <div class="relative w-24 h-24 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden group cursor-pointer" @click="$refs.fileInput.click()">
                  <img v-if="previewImage" :src="previewImage" class="w-full h-full object-contain">
                  <v-icon v-else class="text-slate-300">mdi-camera-plus</v-icon>
                  <div class="absolute inset-0 bg-black/20 hidden group-hover:flex items-center justify-center text-white text-xs">Change</div>
                </div>
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleImageUpload">
             </div>

            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Brand Name</label>
              <input v-model="form.name" placeholder="Name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all"/>
              <p v-if="form.errors.name" class="text-red-500 text-xs ml-1">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Status</label>
              <select v-model="form.status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none cursor-pointer">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button @click="showModal=false" class="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">Cancel</button>
            <button @click="save" :disabled="form.processing" class="px-8 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 shadow-lg shadow-slate-200 active:scale-95 transition-all">
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.animate-pop { animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes pop { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>