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
const openDropdownId = ref(null);

// Close dropdown on click outside
if (typeof window !== 'undefined') {
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) openDropdownId.value = null;
  });
}

// Form for Updating Status
const form = useForm({
  id: null,
  review_status: "",
  // Read-only fields for display in modal
  product_name: "",
  product_image: "",
  user_name: "",
  user_image: "",
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
  form.product_image = item.product_image;
  form.user_name = item.user_name;
  form.user_image = item.user_image;
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

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
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
              <div class="flex justify-end relative">
                <button @click="openDropdownId = openDropdownId === r.id ? null : r.id" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-full transition-all active:scale-90">
                  <v-icon size="20">mdi-dots-vertical</v-icon>
                </button>

                <div v-if="openDropdownId === r.id" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-[50] py-1.5 animate-in fade-in slide-in-from-top-2 duration-200">
                  <button @click="openEditModal(r); openDropdownId = null" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                    <v-icon size="16">mdi-eye-check-outline</v-icon>
                    Review Details
                  </button>
                  <div class="h-[1px] bg-slate-50 my-1"></div>
                  <button @click="deleteItem(r.id); openDropdownId = null" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-rose-500 hover:bg-rose-50 transition-colors">
                    <v-icon size="16">mdi-delete-outline</v-icon>
                    Delete Review
                  </button>
                </div>
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
        <div class="relative bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden animate-pop border border-slate-200">
          <!-- Clean Header -->
          <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white">
            <div>
              <h2 class="text-xl font-black text-slate-800 tracking-tight text-left">Review Details</h2>
            </div>
            <button @click="showModal = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-all active:scale-90">
              <v-icon size="20">mdi-close</v-icon>
            </button>
          </div>
          
          <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto">
             <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
               <!-- Left: Content Card -->
               <div class="space-y-6">
                 <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 space-y-4">
                   <div class="flex items-center gap-4">
                     <div class="w-16 h-16 rounded-xl bg-white border border-slate-100 flex items-center justify-center overflow-hidden shadow-sm">
                       <img v-if="form.product_image" :src="form.product_image" class="w-full h-full object-cover">
                       <v-icon v-else size="24" class="text-slate-300">mdi-package-variant</v-icon>
                     </div>
                     <div>
                       <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Product</div>
                       <div class="text-sm font-black text-slate-700">{{ form.product_name }}</div>
                     </div>
                   </div>

                   <div>
                     <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Customer</div>
                     <div class="flex items-center gap-2">
                       <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-600 border border-emerald-200 overflow-hidden shrink-0">
                         <img v-if="form.user_image" :src="form.user_image" class="w-full h-full object-cover">
                         <span v-else>{{ form.user_name?.charAt(0) }}</span>
                       </div>
                       <span class="text-sm font-bold text-slate-600">{{ form.user_name }}</span>
                     </div>
                   </div>

                   <div>
                     <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Rating</div>
                     <div class="flex items-center gap-3">
                       <div class="flex gap-0.5">
                         <v-icon v-for="n in 5" :key="n" size="20" :class="n <= form.rating ? 'text-amber-400' : 'text-slate-200'">
                           {{ n <= form.rating ? 'mdi-star' : 'mdi-star-outline' }}
                         </v-icon>
                       </div>
                       <span class="text-2xl font-black text-amber-500">{{ form.rating }}<span class="text-sm text-slate-300">/5</span></span>
                     </div>
                   </div>
                 </div>

                 <div class="relative px-6 py-6 bg-emerald-50/30 rounded-2xl border border-emerald-100/50 italic text-slate-600 text-sm leading-relaxed">
                   <v-icon class="absolute -top-3 left-6 text-emerald-200" size="32">mdi-format-quote-open</v-icon>
                   "{{ form.text }}"
                 </div>
               </div>

               <!-- Right: Controls -->
               <div class="space-y-6">
                 <div class="space-y-4">
                   <div class="flex items-center gap-2 mb-1">
                     <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                     <label class="text-sm font-black text-slate-700 uppercase tracking-wider">Update Status</label>
                   </div>
                   
                   <div class="grid grid-cols-1 gap-3">
                     <button @click="form.review_status = 'PENDING'" :class="form.review_status === 'PENDING' ? 'ring-2 ring-orange-500 bg-orange-50 border-orange-200' : 'bg-white border-slate-200 hover:border-orange-200'" class="flex items-center justify-between px-5 py-4 border-2 rounded-2xl transition-all group">
                       <div class="flex items-center gap-3">
                         <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center">
                           <v-icon size="20">mdi-clock-outline</v-icon>
                         </div>
                         <div class="text-left">
                           <div class="text-xs font-black text-slate-700">Pending Approval</div>
                           <div class="text-[10px] text-slate-400 font-medium">Wait for verification</div>
                         </div>
                       </div>
                       <v-icon v-if="form.review_status === 'PENDING'" size="20" class="text-orange-500">mdi-check-circle</v-icon>
                     </button>

                     <button @click="form.review_status = 'APPROVED'" :class="form.review_status === 'APPROVED' ? 'ring-2 ring-emerald-500 bg-emerald-50 border-emerald-200' : 'bg-white border-slate-200 hover:border-emerald-200'" class="flex items-center justify-between px-5 py-4 border-2 rounded-2xl transition-all group">
                       <div class="flex items-center gap-3">
                         <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                           <v-icon size="20">mdi-check-decagram-outline</v-icon>
                         </div>
                         <div class="text-left">
                           <div class="text-xs font-black text-slate-700">Approve Review</div>
                           <div class="text-[10px] text-slate-400 font-medium">Make visible to everyone</div>
                         </div>
                       </div>
                       <v-icon v-if="form.review_status === 'APPROVED'" size="20" class="text-emerald-500">mdi-check-circle</v-icon>
                     </button>

                     <button @click="form.review_status = 'REJECTED'" :class="form.review_status === 'REJECTED' ? 'ring-2 ring-rose-500 bg-rose-50 border-rose-200' : 'bg-white border-slate-200 hover:border-rose-200'" class="flex items-center justify-between px-5 py-4 border-2 rounded-2xl transition-all group">
                       <div class="flex items-center gap-3">
                         <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                           <v-icon size="20">mdi-close-circle-outline</v-icon>
                         </div>
                         <div class="text-left">
                           <div class="text-xs font-black text-slate-700">Reject Review</div>
                           <div class="text-[10px] text-slate-400 font-medium">Hide from product page</div>
                         </div>
                       </div>
                       <v-icon v-if="form.review_status === 'REJECTED'" size="20" class="text-rose-500">mdi-check-circle</v-icon>
                     </button>
                   </div>
                 </div>
               </div>
             </div>
          </div>

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button @click="showModal=false" class="px-6 py-2.5 text-sm font-bold text-slate-400 hover:text-slate-600 active:scale-95 transition-all">Cancel</button>
            <button @click="save" :disabled="form.processing" class="px-10 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 active:scale-95 transition-all flex items-center gap-2">
              <v-icon size="18" v-if="!form.processing">mdi-content-save-check-outline</v-icon>
              <span>{{ form.processing ? 'Updating...' : 'Save Changes' }}</span>
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