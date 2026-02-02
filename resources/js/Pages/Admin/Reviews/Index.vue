<script setup>
import { ref, computed, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, router, Link } from "@inertiajs/vue3";

const props = defineProps({
  reviews: Object,
  filters: Object
});

// State
const search = ref(props.filters?.search || "");
const from_date = ref(props.filters?.from_date || "");
const to_date = ref(props.filters?.to_date || "");
const statusFilter = ref(props.filters?.status || "");
const showModal = ref(false);
const showBulkModal = ref(false);
const selectedIds = ref([]);
const bulkStatus = ref("");

// Form for Updating Status
const form = useForm({
  id: null,
  review_status: "",
  // Read-only fields for display in modal
  product_name: "",
  user_name: "",
  rating: 0,
  text: ""
});

// Form for Bulk Update
const bulkForm = useForm({
  ids: [],
  review_status: ""
});

const filteredReviews = computed(() => props.reviews.data);

// Watch for filters
watch([search, from_date, to_date, statusFilter], ([newSearch, newFrom, newTo, newStatus]) => {
  router.get(route('admin.reviews.index'), { 
    search: newSearch, from_date: newFrom, to_date: newTo, status: newStatus 
  }, { preserveState: true, replace: true });
});

function openEditModal(item) {
  form.id = item.id;
  form.review_status = item.status;
  
  // Set display data
  form.product_name = item.product_name;
  form.user_name = item.user_name;
  form.rating = item.rating;
  form.text = item.text;
  
  showModal.value = true;
}

function save() {
  form.put(route('admin.reviews.update', form.id), { 
    onSuccess: () => showModal.value = false 
  });
}

function deleteItem(id) {
  if(confirm("Delete this review permanently?")) router.delete(route('admin.reviews.destroy', id));
}

function toggleSelection(id) {
  const index = selectedIds.value.indexOf(id);
  if (index > -1) {
    selectedIds.value.splice(index, 1);
  } else {
    selectedIds.value.push(id);
  }
}

function toggleSelectAll() {
  if (selectedIds.value.length === filteredReviews.value.length) {
    selectedIds.value = [];
  } else {
    selectedIds.value = filteredReviews.value.map(r => r.id);
  }
}

function openBulkModal() {
  if (selectedIds.value.length === 0) {
    alert("Please select at least one review");
    return;
  }
  bulkStatus.value = "";
  showBulkModal.value = true;
}

function saveBulk() {
  bulkForm.ids = selectedIds.value;
  bulkForm.review_status = bulkStatus.value;
  
  bulkForm.post(route('admin.reviews.bulk-update'), {
    onSuccess: () => {
      showBulkModal.value = false;
      selectedIds.value = [];
      bulkStatus.value = "";
    }
  });
}

</script>

<template>
  <AdminLayout title="Reviews" subtitle="Manage customer feedback">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-black text-slate-800 tracking-tight">Reviews</h1>
      <div class="flex gap-2">
        <button v-if="selectedIds.length > 0" @click="openBulkModal" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all flex items-center gap-2">
          <v-icon size="18">mdi-check-all</v-icon>
          <span class="text-sm font-bold">Bulk Edit ({{ selectedIds.length }})</span>
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm backdrop-blur-xl">
      <div class="flex flex-col md:flex-row items-center gap-2">
        <div class="relative w-full md:w-1/3">
          <input v-model="search" type="text" placeholder="Search review #, product, or user..." class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-sm focus:ring-4 focus:ring-green-500/10 transition-all placeholder:text-slate-400 font-medium"/>
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
        </div>

        <select v-model="statusFilter" class="px-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-xs font-bold text-slate-500 outline-none focus:ring-2 focus:ring-green-500/20">
             <option value="">All Statuses</option>
             <option value="PENDING">Pending</option>
             <option value="APPROVED">Approved</option>
             <option value="REJECTED">Rejected</option>
        </select>

        <button v-if="search || from_date || to_date || statusFilter" @click="search=''; from_date=''; to_date=''; statusFilter=''" class="ml-2 mr-2 w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 active:scale-90 transition-all"><v-icon size="16">mdi-filter-off</v-icon></button>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            <th class="px-6 py-3">
              <input type="checkbox" :checked="selectedIds.length === filteredReviews.length && filteredReviews.length > 0" @change="toggleSelectAll" class="rounded border-slate-300 text-green-600 focus:ring-green-500">
            </th>
            <th class="px-6 py-3">Product</th>
            <th class="px-6 py-3">User</th>
            <th class="px-6 py-3">Rating</th>
            <th class="px-6 py-3 w-1/3">Comment</th>
            <th class="px-6 py-3 text-center">Status</th>
            <th class="px-6 py-3">Date</th>
            <th class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="r in filteredReviews" :key="r.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <input type="checkbox" :checked="selectedIds.includes(r.id)" @change="toggleSelection(r.id)" class="rounded border-slate-300 text-green-600 focus:ring-green-500">
            </td>
            <td class="px-6 py-4">
               <div class="flex items-center gap-3">
                 <div class="w-8 h-8 rounded bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shrink-0">
                    <img v-if="r.product_image" :src="r.product_image" class="w-full h-full object-cover">
                    <v-icon v-else size="14" class="text-gray-300">mdi-package-variant</v-icon>
                 </div>
                 <div>
                   <div class="text-xs font-bold text-slate-700 line-clamp-1">{{ r.product_name }}</div>
                   <div class="text-[10px] font-mono text-slate-400">#{{ r.review_number }}</div>
                 </div>
               </div>
            </td>
            <td class="px-6 py-4 text-xs font-bold text-slate-600">{{ r.user_name }}</td>
            <td class="px-6 py-4">
               <div class="flex items-center gap-2">
                 <div class="flex">
                   <v-icon v-for="n in 5" :key="n" size="16" :class="n <= r.rating ? 'text-amber-400' : 'text-slate-300'">
                     {{ n <= r.rating ? 'mdi-star' : 'mdi-star-outline' }}
                   </v-icon>
                 </div>
                 <span class="text-xs font-black text-slate-700">{{ r.rating }}/5</span>
               </div>
            </td>
            <td class="px-6 py-4 text-xs text-slate-500 italic line-clamp-2">"{{ r.text }}"</td>
            <td class="px-6 py-4 text-center">
               <span :class="{'bg-green-100 text-green-700': r.status==='APPROVED', 'bg-orange-50 text-orange-600': r.status==='PENDING', 'bg-red-50 text-red-500': r.status==='REJECTED'}" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">{{ r.status }}</span>
            </td>
            <td class="px-6 py-4 text-[10px] text-slate-400 font-bold">{{ r.created_at }}</td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button @click="openEditModal(r)" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"><v-icon size="18">mdi-eye-check-outline</v-icon></button>
                <button @click="deleteItem(r.id)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg"><v-icon size="18">mdi-delete</v-icon></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
       <div v-if="reviews.links" class="p-3 flex justify-center gap-1">
         <Link v-for="(link, i) in reviews.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'" />
      </div>
    </div>

    <!-- MODAL -->
    <Transition name="modal-fade">
      <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showModal = false"></div>
        <div class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-pop flex flex-col max-h-[90vh]">
          <div class="bg-gradient-to-tr from-green-600 to-emerald-400 px-8 py-6 text-white shrink-0">
            <h2 class="text-xl font-black tracking-tight">Review Details</h2>
          </div>
          
          <div class="p-8 space-y-6 overflow-y-auto custom-scrollbar">
             <!-- Read Only Details -->
             <div class="bg-slate-50 p-4 rounded-2xl space-y-3 border border-slate-100">
                <div class="flex justify-between items-start">
                   <div>
                      <div class="text-[10px] font-bold text-slate-400 uppercase">Product</div>
                      <div class="font-bold text-slate-700">{{ form.product_name }}</div>
                   </div>
                   <div class="text-right">
                      <div class="text-[10px] font-bold text-slate-400 uppercase">User</div>
                      <div class="font-bold text-slate-700">{{ form.user_name }}</div>
                   </div>
                </div>
                <div>
                   <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Rating</div>
                   <div class="flex items-center gap-3">
                     <div class="flex">
                       <v-icon v-for="n in 5" :key="n" size="18" :class="n <= form.rating ? 'text-amber-400' : 'text-slate-300'">
                         {{ n <= form.rating ? 'mdi-star' : 'mdi-star-outline' }}
                       </v-icon>
                     </div>
                     <span class="text-2xl font-black text-amber-500">{{ form.rating }}<span class="text-sm text-slate-400">/5</span></span>
                   </div>
                </div>
                <div>
                   <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Review Content</div>
                   <p class="text-sm text-slate-600 italic leading-relaxed">"{{ form.text }}"</p>
                </div>
             </div>

             <!-- Edit Status -->
             <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Update Status</label>
                <select v-model="form.review_status" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all">
                  <option value="PENDING">Pending Approval</option>
                  <option value="APPROVED">Approved (Visible)</option>
                  <option value="REJECTED">Rejected (Hidden)</option>
                </select>
                <div v-if="form.errors.review_status" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.review_status }}</div>
             </div>
          </div>

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
            <button @click="showModal=false" class="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600">Close</button>
            <button @click="save" :disabled="form.processing" class="px-8 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 shadow-lg active:scale-95 transition-all">
              <span v-if="form.processing">Updating...</span>
              <span v-else>Update Status</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- BULK EDIT MODAL -->
    <Transition name="modal-fade">
      <div v-if="showBulkModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showBulkModal = false"></div>
        <div class="relative bg-white w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-pop">
          <div class="bg-gradient-to-tr from-green-600 to-emerald-400 px-8 py-6 text-white">
            <h2 class="text-xl font-black tracking-tight">Bulk Update Status</h2>
            <p class="text-sm text-white/80 mt-1">Update {{ selectedIds.length }} selected review(s)</p>
          </div>
          
          <div class="p-8 space-y-6">
             <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Select New Status</label>
                <select v-model="bulkStatus" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all">
                  <option value="">-- Choose Status --</option>
                  <option value="PENDING">Pending Approval</option>
                  <option value="APPROVED">Approved (Visible)</option>
                  <option value="REJECTED">Rejected (Hidden)</option>
                </select>
             </div>
          </div>

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button @click="showBulkModal=false" class="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600">Cancel</button>
            <button @click="saveBulk" :disabled="!bulkStatus || bulkForm.processing" class="px-8 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 shadow-lg active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="bulkForm.processing">Updating...</span>
              <span v-else">Update All</span>
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