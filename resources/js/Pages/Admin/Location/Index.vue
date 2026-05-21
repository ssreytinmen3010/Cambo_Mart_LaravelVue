<script setup>
import { ref, computed, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, router } from "@inertiajs/vue3";

const props = defineProps({
  locations: Array,
});

// State
const searchName = ref("");
const showModal = ref(false);
const isEdit = ref(false);
const showViewModal = ref(false);
const viewLocation = ref(null);

// Form for Create/Edit
const form = useForm({
    id: null,
    name: "",
    address: "",
    phone: "",
    map_lat: "",
    map_long: "",
    is_active: true
    });

// Search functionality
const filteredLocations = computed(() => {
  if (!searchName.value) return props.locations;
  return props.locations.filter(loc => 
    loc.name.toLowerCase().includes(searchName.value.toLowerCase()) ||
    loc.address.toLowerCase().includes(searchName.value.toLowerCase())
  );
});

// Actions
function openAddModal() {
    isEdit.value = false;
    form.reset();
    form.is_active = true;
    showModal.value = true;
}

function openEditModal(location) {
    isEdit.value = true;
    form.id = location.id;
    form.name = location.name;
    form.address = location.address;
    form.phone = location.phone;
    form.map_lat = location.map_lat;
    form.map_long = location.map_long;
    form.is_active = !!location.is_active;
    showModal.value = true;
    }

    function openViewModal(location) {
        viewLocation.value = location;
        showViewModal.value = true;
    }

function save() {
    if (isEdit.value) {
        form.put(route('admin.locations.update', form.id), {
        onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('admin.locations.store'), {
        onSuccess: () => showModal.value = false
        });
    }
    }

function deleteItem(id) {
    if(confirm("Are you sure you want to delete this location?")) {
        router.delete(route('admin.locations.destroy', id));
    }
}

// Map logic
const mapUrl = computed(() => {
    const lat = isEdit.value ? form.map_lat : (viewLocation.value?.map_lat || form.map_lat);
    const lng = isEdit.value ? form.map_long : (viewLocation.value?.map_long || form.map_long);
    
    if (!lat || !lng) return null;
    return `https://www.google.com/maps?q=${lat},${lng}&hl=es;z=14&output=embed`;
});

// Dropdown state
const activeDropdown = ref(null);
function toggleDropdown(id) {
  activeDropdown.value = activeDropdown.value === id ? null : id;
}

// Close dropdown on click outside
if (typeof window !== 'undefined') {
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown-trigger') && !e.target.closest('.dropdown-menu')) {
      activeDropdown.value = null;
    }
  });
}
</script>

<template>
  <AdminLayout title="Locations" subtitle="Manage store locations">
    <div class="p-6">
      <!-- HEADER ACTIONS -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="relative w-full md:w-96 group">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <v-icon icon="mdi-magnify" size="small" class="text-slate-400 group-focus-within:text-emerald-500 transition-colors" />
          </span>
          <input 
            v-model="searchName"
            type="text" 
            placeholder="Search by name or address..." 
            class="block w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none"
          />
        </div>

        <button 
          @click="openAddModal"
          class="flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-all active:scale-95 shadow-lg shadow-emerald-500/20"
        >
          <v-icon icon="mdi-plus" size="small" />
          Add Location
        </button>
      </div>

      <!-- LOCATIONS TABLE -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-visible">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-200">
              <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">Location Name</th>
              <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">Contact Info</th>
              <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
              <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="loc in filteredLocations" :key="loc.id" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-700 leading-tight">{{ loc.name }}</span>
                  <span class="text-[11px] text-slate-400 mt-1 line-clamp-1 italic">{{ loc.address }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                {{ loc.phone || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-center">
                <span 
                  :class="loc.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100'"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black border uppercase tracking-wider"
                >
                  <span v-if="loc.is_active" class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                  {{ loc.is_active ? 'Active' : 'Hidden' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="relative inline-block text-left">
                  <button @click.stop="toggleDropdown(loc.id)" class="dropdown-trigger p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400">
                    <v-icon icon="mdi-dots-vertical" />
                  </button>
                  
                  <div v-if="activeDropdown === loc.id" class="dropdown-menu absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none py-1 border border-slate-100 animate-in fade-in zoom-in duration-150">
                    <button @click="openViewModal(loc)" class="flex items-center w-full px-4 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                      <v-icon icon="mdi-eye-outline" size="small" class="mr-3" /> View Detail
                    </button>
                    <button @click="openEditModal(loc)" class="flex items-center w-full px-4 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                      <v-icon icon="mdi-pencil-outline" size="small" class="mr-3" /> Edit Location
                    </button>
                    <div class="border-t border-slate-50 my-1"></div>
                    <button @click="deleteItem(loc.id)" class="flex items-center w-full px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors font-bold">
                      <v-icon icon="mdi-trash-can-outline" size="small" class="mr-3" /> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- CREATE/EDIT MODAL -->
      <Transition 
        enter-active-class="duration-300 ease-out" 
        enter-from-class="opacity-0 scale-95" 
        enter-to-class="opacity-100 scale-100" 
        leave-active-class="duration-200 ease-in" 
        leave-from-class="opacity-100 scale-100" 
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
          <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden flex flex-col transition-all">
            <!-- Modal Header -->
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between shrink-0">
              <div>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ isEdit ? 'Edit Location' : 'New Location' }}</h2>
                <p class="text-[12px] text-slate-500 font-medium">Manage your store presence and coordinates</p>
              </div>
              <button @click="showModal = false" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                <v-icon icon="mdi-close" size="small" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-8 overflow-y-auto max-h-[70vh]">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Side: Form -->
                <div class="space-y-6">
                  <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Location Name</label>
                    <div class="relative">
                      <v-icon icon="mdi-store-outline" size="small" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                      <input v-model="form.name" type="text" placeholder="e.g. Main Branch" class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none text-slate-700 font-bold placeholder:font-normal" />
                    </div>
                    <p v-if="form.errors.name" class="text-[10px] text-rose-500 font-bold mt-1 pl-1">{{ form.errors.name }}</p>
                  </div>

                  <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Store Address</label>
                    <textarea v-model="form.address" rows="3" placeholder="Enter full physical address..." class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none text-slate-700 font-bold placeholder:font-normal resize-none"></textarea>
                    <p v-if="form.errors.address" class="text-[10px] text-rose-500 font-bold mt-1 pl-1">{{ form.errors.address }}</p>
                  </div>

                  <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Contact Phone</label>
                    <div class="relative">
                      <v-icon icon="mdi-phone-outline" size="small" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                      <input v-model="form.phone" type="text" placeholder="e.g. 012 345 678" class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none text-slate-700 font-bold placeholder:font-normal" />
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                      <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Latitude</label>
                      <input v-model="form.map_lat" type="text" placeholder="11.55..." class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none text-slate-700 font-bold placeholder:font-normal" />
                    </div>
                    <div class="space-y-2">
                      <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Longitude</label>
                      <input v-model="form.map_long" type="text" placeholder="104.9..." class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none text-slate-700 font-bold placeholder:font-normal" />
                    </div>
                  </div>

                  <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl">
                    <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-200" id="is_active" />
                    <label for="is_active" class="text-sm font-bold text-slate-700 cursor-pointer">Visibile on Public Map</label>
                  </div>
                </div>

                <!-- Right Side: Preview Map -->
                <div class="flex flex-col gap-4">
                  <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Live Map Preview</label>
                  <div class="flex-1 bg-slate-100 rounded-3xl overflow-hidden border border-slate-200 relative min-h-[300px]">
                    <iframe 
                      v-if="mapUrl"
                      :src="mapUrl"
                      class="w-full h-full border-none"
                      allowfullscreen=""
                      loading="lazy"
                    ></iframe>
                    <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                      <v-icon icon="mdi-map-marker-off-outline" size="x-large" class="mb-2" />
                      <span class="text-[10px] font-black uppercase tracking-widest">No Coordinates Set</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between shrink-0">
               <span class="text-[11px] text-slate-400 font-bold italic">Unsaved changes will be lost</span>
               <div class="flex gap-3">
                 <button @click="showModal = false" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all">Cancel</button>
                 <button @click="save" :disabled="form.processing" class="px-8 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-500/20 disabled:opacity-50">
                   {{ isEdit ? 'Update Location' : 'Create Location' }}
                 </button>
               </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- VIEW DETAIL MODAL -->
      <Transition 
        enter-active-class="duration-300 ease-out" 
        enter-from-class="opacity-0 scale-95" 
        enter-to-class="opacity-100 scale-100" 
        leave-active-class="duration-200 ease-in" 
        leave-from-class="opacity-100 scale-100" 
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showViewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
          <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden flex flex-col transition-all overflow-y-auto max-h-[90vh]">
            <div class="p-10">
               <div class="flex items-start justify-between mb-10">
                  <div class="flex items-center gap-6">
                    <div class="w-20 h-20 rounded-3xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 text-3xl font-black shadow-inner">
                      {{ viewLocation.name.charAt(0) }}
                    </div>
                    <div>
                      <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2">{{ viewLocation.name }}</h2>
                      <div class="flex items-center gap-2">
                        <span 
                          :class="viewLocation.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400'"
                          class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-current opacity-70"
                        >
                          {{ viewLocation.is_active ? 'Active Branch' : 'Hidden Branch' }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-bold">• Branch ID: #LOC-{{ viewLocation.id }}</span>
                      </div>
                    </div>
                  </div>
                  <button @click="showViewModal = false" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                    <v-icon icon="mdi-close" size="large" />
                  </button>
               </div>

               <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                  <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 flex items-start gap-4">
                    <v-icon icon="mdi-map-marker-radius-outline" class="text-emerald-500 mt-1" />
                    <div>
                      <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Address</label>
                      <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ viewLocation.address }}</p>
                    </div>
                  </div>
                  <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 flex items-start gap-4">
                    <v-icon icon="mdi-phone-outline" class="text-emerald-500 mt-1" />
                    <div>
                      <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Contact</label>
                      <p class="text-sm font-bold text-slate-700">{{ viewLocation.phone || 'No phone provided' }}</p>
                    </div>
                  </div>
                  <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 flex items-start gap-4">
                    <v-icon icon="mdi-earth" class="text-emerald-500 mt-1" />
                    <div>
                      <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Coordinates</label>
                      <p class="text-[11px] font-bold text-slate-700">{{ viewLocation.map_lat }}, {{ viewLocation.map_long }}</p>
                    </div>
                  </div>
               </div>

               <div class="h-96 rounded-[40px] overflow-hidden border-8 border-slate-50 shadow-inner relative">
                  <iframe 
                    v-if="viewLocation.map_lat"
                    :src="mapUrl"
                    class="w-full h-full border-none"
                    allowfullscreen=""
                    loading="lazy"
                  ></iframe>
                   <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100 text-slate-400 italic font-bold">
                     No Map Preview Available
                   </div>
               </div>
               
               <div class="mt-10 flex justify-end gap-3">
                 <button @click="showViewModal = false" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-bold transition-all shadow-xl shadow-emerald-500/20">
                   Close Detailed View
                 </button>
               </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </AdminLayout>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: .7; transform: scale(1.2); }
}
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>