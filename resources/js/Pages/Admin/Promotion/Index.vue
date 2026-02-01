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
  if(confirm("Delete promotion?")) router.delete(route('admin.promotions.destroy', id));
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

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
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
               <span :class="{'bg-green-100 text-green-700': p.status==='ACTIVE', 'bg-yellow-50 text-yellow-600': p.status==='PAUSED', 'bg-gray-100 text-gray-500': p.status==='DRAFT'}" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">{{ p.status }}</span>
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
       <div v-if="promotions.links" class="p-3 flex justify-center gap-1">
         <Link v-for="(link, i) in promotions.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'" />
      </div>
    </div>

    <!-- MODAL -->
    <Transition name="modal-fade">
      <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showModal = false"></div>
        <div class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-pop flex flex-col max-h-[90vh]">
          <div class="bg-gradient-to-tr from-green-600 to-emerald-400 px-8 py-6 text-white shrink-0">
            <h2 class="text-xl font-black tracking-tight">{{ isEdit ? 'Edit Promotion' : 'New Promotion' }}</h2>
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

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
            <button @click="showModal=false" class="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600">Cancel</button>
            <button @click="save" :disabled="form.processing" class="px-8 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 shadow-lg active:scale-95 transition-all">
              <span v-if="form.processing">Saving...</span>
              <span v-else>Save</span>
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