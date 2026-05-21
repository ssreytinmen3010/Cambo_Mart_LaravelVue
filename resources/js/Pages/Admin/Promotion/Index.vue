<script setup>
import { ref, computed, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, router, Link } from "@inertiajs/vue3";

const props = defineProps({
  promotions: Object,
  products: Array,
  filters: Object
});

// State
const search = ref(props.filters?.search || "");
const from_date = ref(props.filters?.from_date || "");
const to_date = ref(props.filters?.to_date || "");
const showModal = ref(false);
const isEdit = ref(false);
const showViewModal = ref(false);
const viewItem = ref(null);
const activeDropdown = ref(null);

const form = useForm({
  id: null,
  code: "",
  name: "",
  promo_type: "PERCENTAGE", // Default
  discount_value: "",
  product_id: "",
  buy_qty: "",
  get_qty: "",
  status: "DRAFT",
  start_date: "",
  end_date: ""
});

const filteredPromotions = computed(() => props.promotions.data);

// Watch for filters
watch([search, from_date, to_date], ([newSearch, newFrom, newTo]) => {
  router.get(route('admin.promotions.index'), { 
    search: newSearch, from_date: newFrom, to_date: newTo 
  }, { preserveState: true, replace: true });
});

// Actions
function openAddModal() {
  isEdit.value = false;
  form.reset();
  form.status = 'DRAFT';
  form.promo_type = 'PERCENTAGE';
  showModal.value = true;
}

function openEditModal(item) {
  isEdit.value = true;
  form.id = item.id;
  form.code = item.code;
  form.name = item.name;
  form.promo_type = item.type;
  form.discount_value = item.value;
  form.product_id = item.product_id || "";
  form.buy_qty = item.buy_qty;
  form.get_qty = item.get_qty;
  form.status = item.status;
  form.start_date = item.start_date;
  form.end_date = item.end_date;
  showModal.value = true;
}

function openViewModal(item) {
  viewItem.value = item;
  showViewModal.value = true;
}

function save() {
  const routeName = isEdit.value ? 'admin.promotions.update' : 'admin.promotions.store';
  
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
  if(confirm("Delete promotion?")) router.delete(route('admin.promotions.destroy', id), {
    onSuccess: () => activeDropdown.value = null
  });
}

function toggleDropdown(id) {
  activeDropdown.value = activeDropdown.value === id ? null : id;
}

// Close dropdown on click outside
if (typeof window !== 'undefined') {
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown-container')) {
      activeDropdown.value = null;
    }
  });
}
</script>

<template>
  <AdminLayout title="Promotions" subtitle="Manage discounts">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-black text-slate-800 tracking-tight">Promotions</h1>
      <button @click="openAddModal" class="px-3 py-1.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-green-500/20 transition-all flex items-center gap-1.5 active:scale-95">
        <v-icon color="white" size="14">mdi-plus</v-icon> New Promo
      </button>
    </div>

    <!-- Filter Bar -->
    <div class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm backdrop-blur-xl">
      <div class="flex flex-col md:flex-row items-center gap-2">
        <div class="relative w-full md:w-1/3">
          <input v-model="search" type="text" placeholder="Search name or code..." class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-sm focus:ring-4 focus:ring-green-500/10 transition-all placeholder:text-slate-400 font-medium"/>
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

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            <th class="px-6 py-3">Code / Name</th>
            <th class="px-6 py-3">Type</th>
            <th class="px-6 py-3">Value</th>
            <th class="px-6 py-3">Product Scope</th>
            <th class="px-6 py-3">Duration</th>
            <th class="px-6 py-3 text-center">Status</th>
            <th class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="p in filteredPromotions" :key="p.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
               <div class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded inline-block mb-1">{{ p.code }}</div>
               <div class="text-sm font-bold text-slate-600">{{ p.name }}</div>
            </td>
            <td class="px-6 py-4 text-xs font-bold text-slate-500">{{ p.type }}</td>
            <td class="px-6 py-4 text-sm font-bold text-green-600">
               {{ p.description }}
            </td>
            <td class="px-6 py-4 text-xs text-slate-500">
               <span v-if="p.product_id" class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full font-bold uppercase text-[9px]">{{ p.product_name }}</span>
               <span v-else class="text-slate-300 italic">All Products</span>
            </td>
            <td class="px-6 py-4 text-[10px] text-slate-400">
               <div v-if="p.start_date">{{ p.start_date }} <span class="text-slate-300">to</span> {{ p.end_date }}</div>
               <div v-else class="italic">Permanent</div>
            </td>
            <td class="px-6 py-4 text-center">
               <div class="flex items-center justify-center gap-1.5">
                 <div v-if="p.status === 'ACTIVE'" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                 <span :class="{'bg-green-100 text-green-700': p.status==='ACTIVE', 'bg-yellow-50 text-yellow-600': p.status==='PAUSED', 'bg-gray-100 text-gray-500': p.status==='DRAFT'}" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">{{ p.status }}</span>
               </div>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end relative dropdown-container">
                <button @click.stop="toggleDropdown(p.id)" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all active:scale-90">
                  <v-icon size="20">mdi-dots-vertical</v-icon>
                </button>

                <!-- Dropdown Menu -->
                <div v-if="activeDropdown === p.id" class="absolute right-10 top-0 z-50 w-44 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 py-2 animate-in fade-in slide-in-from-right-2 duration-200">
                  <button @click="openViewModal(p); activeDropdown = null" class="w-full px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors text-xs font-bold">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                      <v-icon size="16">mdi-eye-outline</v-icon>
                    </div>
                    View Detail
                  </button>

                  <button @click="openEditModal(p); activeDropdown = null" class="w-full px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors text-xs font-bold border-t border-slate-50">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                      <v-icon size="16">mdi-pencil-outline</v-icon>
                    </div>
                    Edit Promo
                  </button>

                  <button @click="deleteItem(p.id); activeDropdown = null" class="w-full px-4 py-2.5 flex items-center gap-3 text-rose-500 hover:bg-rose-50 transition-colors text-xs font-bold border-t border-slate-50">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
                      <v-icon size="16">mdi-trash-can-outline</v-icon>
                    </div>
                    Delete
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
       <div v-if="promotions.links" class="p-3 flex justify-center gap-1">
         <Link v-for="(link, i) in promotions.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'" />
      </div>
    </div>

    <Transition name="modal-fade">
      <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showModal = false"></div>
        <div class="relative bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden animate-pop flex flex-col max-h-[90vh]">
          <div class="px-8 py-6 text-white shrink-0 flex items-center justify-between border-b border-slate-100">
            <div>
              <h2 class="text-xl font-black tracking-tight text-slate-800">{{ isEdit ? 'Edit Promotion' : 'New Promotion' }}</h2>
            </div>
            <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
              <v-icon size="20" class="text-slate-400">mdi-close</v-icon>
            </button>
          </div>
          
          <div class="p-8 space-y-4 overflow-y-auto custom-scrollbar">
             <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                   <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Promo Code</label>
                   <input v-model="form.code" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
                   <div v-if="form.errors.code" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.code }}</div>
                </div>
                <div class="space-y-1">
                   <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Name</label>
                   <input v-model="form.name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
                   <div v-if="form.errors.name" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.name }}</div>
                </div>
             </div>

             <div class="grid grid-cols-2 gap-3">
               <div class="space-y-1">
                 <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Type</label>
                 <select v-model="form.promo_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                    <option value="PERCENTAGE">Percentage (%)</option>
                    <option value="BOGO">Buy X Get Y</option>
                 </select>
                 <div v-if="form.errors.promo_type" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.promo_type }}</div>
               </div>
               <div class="space-y-1" v-if="form.promo_type === 'PERCENTAGE'">
                 <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Percentage Value</label>
                 <input v-model="form.discount_value" type="number" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
                 <div v-if="form.errors.discount_value" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.discount_value }}</div>
               </div>
             </div>

             <div class="grid grid-cols-2 gap-3" v-if="form.promo_type === 'BOGO'">
                <div class="space-y-1">
                   <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Buy Qty</label>
                   <input v-model="form.buy_qty" type="number" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
                   <div v-if="form.errors.buy_qty" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.buy_qty }}</div>
                </div>
                <div class="space-y-1">
                   <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Get Qty</label>
                   <input v-model="form.get_qty" type="number" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
                   <div v-if="form.errors.get_qty" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.get_qty }}</div>
                </div>
             </div>

             <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Specific Product (Optional)</label>
                <select v-model="form.product_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                  <option value="">Global (All Products)</option>
                  <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <div v-if="form.errors.product_id" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.product_id }}</div>
             </div>
            
             <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                   <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Start Date</label>
                   <input v-model="form.start_date" type="date" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
                   <div v-if="form.errors.start_date" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.start_date }}</div>
                </div>
                <div class="space-y-1">
                   <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">End Date</label>
                   <input v-model="form.end_date" type="date" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"/>
                   <div v-if="form.errors.end_date" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.end_date }}</div>
                </div>
             </div>

             <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Status</label>
                <select v-model="form.status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                  <option value="DRAFT">Draft</option>
                  <option value="ACTIVE">Active</option>
                  <option value="PAUSED">Paused</option>
                  <option value="EXPIRED">Expired</option>
                </select>
                <div v-if="form.errors.status" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.status }}</div>
             </div>
          </div>

          <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
             <button @click="showModal = false" class="px-6 py-2.5 text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest">Cancel</button>
             <button @click="save" :disabled="form.processing" class="px-8 py-3 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-black rounded-2xl shadow-lg shadow-green-500/20 hover:shadow-green-500/40 transition-all active:scale-95 uppercase tracking-widest flex items-center gap-2">
                <v-icon v-if="form.processing" size="14" class="animate-spin">mdi-loading</v-icon>
                {{ isEdit ? 'Save Changes' : 'Create Promotion' }}
             </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- VIEW PROMOTION MODAL -->
    <Transition name="modal-fade">
      <div v-if="showViewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showViewModal = false"></div>
        <div class="relative bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden animate-pop flex flex-col">
          <!-- Header -->
          <div class="px-8 py-6 flex items-center justify-between border-b border-slate-100">
            <div>
              <h2 class="text-xl font-black tracking-tight text-slate-800">Promotion Details</h2>
            </div>
            <button @click="showViewModal = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 hover:bg-emerald-100 transition-colors group">
              <v-icon size="20" class="text-emerald-600 transition-transform group-hover:rotate-90">mdi-close</v-icon>
            </button>
          </div>

          <!-- Body -->
          <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 overflow-y-auto max-h-[70vh]">
            <!-- Left Side: Basic Info -->
            <div class="space-y-6">
              <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-4 mb-6">
                  <div class="w-16 h-16 rounded-2xl bg-emerald-100 flex items-center justify-center">
                    <v-icon size="32" class="text-emerald-600">mdi-tag-outline</v-icon>
                  </div>
                  <div>
                    <div class="text-xs font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md inline-block mb-1">{{ viewItem?.code }}</div>
                    <h3 class="text-lg font-black text-slate-800">{{ viewItem?.name }}</h3>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="p-4 bg-white rounded-xl border border-slate-100">
                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Promo Type</label>
                    <span class="text-sm font-bold text-slate-700">{{ viewItem?.type }}</span>
                  </div>
                  <div class="p-4 bg-white rounded-xl border border-slate-100">
                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Benefit</label>
                    <span class="text-sm font-bold text-green-600">{{ viewItem?.description }}</span>
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <div class="flex items-center gap-3">
                  <div class="w-2.5 h-2.5 rounded-full relative" :class="viewItem?.status === 'ACTIVE' ? 'bg-emerald-500' : 'bg-rose-500'">
                    <div class="absolute inset-0 rounded-full animate-ping opacity-75" :class="viewItem?.status === 'ACTIVE' ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                  </div>
                  <span class="text-sm font-black uppercase tracking-wider" :class="viewItem?.status === 'ACTIVE' ? 'text-emerald-600' : 'text-rose-600'">
                    {{ viewItem?.status }} STATUS
                  </span>
                </div>
                
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 space-y-4">
                  <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-500">Applicable Product</label>
                    <span class="text-xs font-bold" :class="viewItem?.product_name ? 'text-blue-600' : 'text-slate-400 italic'">
                      {{ viewItem?.product_name || 'Global (All Products)' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Side: Period & Details -->
            <div class="space-y-6">
              <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 space-y-6">
                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest border-b border-slate-200 pb-3">Campaign Period</h4>
                
                <div class="space-y-4">
                  <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center shrink-0">
                      <v-icon size="20" class="text-slate-400">mdi-calendar-start</v-icon>
                    </div>
                    <div>
                      <label class="text-[10px] font-bold text-slate-400 uppercase block">Start Date</label>
                      <span class="text-sm font-bold text-slate-700">{{ viewItem?.start_date || 'Permanent' }}</span>
                    </div>
                  </div>

                  <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center shrink-0">
                      <v-icon size="20" class="text-slate-400">mdi-calendar-end</v-icon>
                    </div>
                    <div>
                      <label class="text-[10px] font-bold text-slate-400 uppercase block">End Date</label>
                      <span class="text-sm font-bold text-slate-700">{{ viewItem?.end_date || 'Permanent' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="viewItem?.type === 'BOGO'" class="bg-emerald-50 p-6 rounded-2xl border border-emerald-100">
                <div class="flex items-center gap-3 text-emerald-700">
                  <v-icon size="20">mdi-gift-outline</v-icon>
                  <span class="text-sm font-bold uppercase tracking-tight">BOGO Strategy</span>
                </div>
                <div class="mt-3 text-2xl font-black text-emerald-800">
                  Buy {{ viewItem?.buy_qty }} Get {{ viewItem?.get_qty }} FREE
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
            <button @click="showViewModal = false" class="px-8 py-3 bg-emerald-600 text-white text-xs font-black rounded-2xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-700 transition-all active:scale-95 uppercase tracking-widest">
              Close Detailed View
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