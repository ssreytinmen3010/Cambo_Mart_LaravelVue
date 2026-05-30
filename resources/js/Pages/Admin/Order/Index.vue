<script setup>
import { ref, computed, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, router, Link } from "@inertiajs/vue3";

const props = defineProps({
  orders: Object,
  filters: Object
});

// State
const search = ref(props.filters?.search || "");
const from_date = ref(props.filters?.from_date || "");
const to_date = ref(props.filters?.to_date || "");
const statusFilter = ref(props.filters?.status || "");

const showViewModal = ref(false);
const viewOrder = ref(null);
const expandedItemsOrderId = ref(null);

const filteredOrders = computed(() => props.orders.data);

watch([search, from_date, to_date, statusFilter], ([newSearch, newFrom, newTo, newStatus]) => {
  router.get(route('admin.orders.index'), { 
    search: newSearch, from_date: newFrom, to_date: newTo, status: newStatus 
  }, { preserveState: true, replace: true });
});

function openViewModal(order) {
  viewOrder.value = order;
  showViewModal.value = true;
}

function updateStatus(order, type, newStatus) {
  if(!confirm(`Change ${type} status to ${newStatus}?`)) return;
  
  router.put(route('admin.orders.update', order.id), {
     order_status: type === 'Order' ? newStatus : order.order_status,
     payment_status: type === 'Payment' ? newStatus : order.payment_status
  }, {
    onSuccess: () => {
      if (viewOrder.value && viewOrder.value.id === order.id) {
        // Find the updated order in props and sync viewOrder
        const updated = props.orders.data.find(o => o.id === order.id);
        if (updated) viewOrder.value = updated;
      }
    }
  });
}

function deleteItem(id) {
  if(confirm("Delete this order? This cannot be undone.")) router.delete(route('admin.orders.destroy', id));
}

function toggleItems(orderId) {
  expandedItemsOrderId.value = expandedItemsOrderId.value === orderId ? null : orderId;
}

function getItemCode(item) {
  return item?.product_code ?? item?.code ?? item?.product?.product_code ?? item?.product?.code ?? "-";
}
</script>

<template>
  <AdminLayout title="Orders" subtitle="Manage customer orders">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-black text-slate-800 tracking-tight">Order Management</h1>
      <!-- Bulk Actions could go here -->
    </div>

    <!-- Filter Bar -->
    <div class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm backdrop-blur-xl">
      <div class="flex flex-col md:flex-row items-center gap-2">
        <div class="relative w-full md:w-1/3">
           <input v-model="search" type="text" placeholder="Order # or Customer..." class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-sm focus:ring-4 focus:ring-green-500/10 transition-all placeholder:text-slate-400 font-medium"/>
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

        <!-- Status Filter -->
        <select v-model="statusFilter" class="px-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-xs font-bold text-slate-500 outline-none focus:ring-2 focus:ring-green-500/20">
             <option value="">All Statuses</option>
             <option value="PENDING">Pending</option>
             <option value="COMPLETED">Completed</option>
             <option value="CANCELLED">Cancelled</option>
        </select>
        
        <button v-if="search || from_date || to_date || statusFilter" @click="search=''; from_date=''; to_date=''; statusFilter=''" class="ml-2 w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 transition-all"><v-icon size="16">mdi-filter-off</v-icon></button>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-[1400px] divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            <th class="px-6 py-3">Order</th>
            <th class="px-6 py-3">Customer</th>
            <th class="px-6 py-3">Address</th>
            <!-- <th class="px-6 py-3">Promotion</th> -->
            <th class="px-6 py-3 text-center">Items</th>
            <th class="px-6 py-3 text-right">Subtotal</th>
            <th class="px-6 py-3 text-right">Discount</th>
            <th class="px-6 py-3 text-right">Total</th>
            <th class="px-6 py-3 text-center">Method</th>
            <th class="px-6 py-3 text-center">Order Status</th>
            <th class="px-6 py-3 text-center white-space: nowrap">Payment</th>
            <th class="px-6 py-3">Date</th>
            <th class="px-6 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="o in filteredOrders" :key="o.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <span class="text-xs font-black text-slate-800">#{{ o.order_number }}</span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold border border-emerald-100 overflow-hidden shrink-0 shadow-sm">
                  <img v-if="o.customer_image" :src="o.customer_image" class="w-full h-full object-cover">
                  <span v-else>{{ o.customer_name.charAt(0) }}</span>
                </div>
                <span class="text-xs font-bold text-slate-800 tracking-tight">{{ o.customer_name }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-[11px] text-slate-600 font-medium max-w-[240px] truncate" :title="o.customer_address || ''">
                {{ o.customer_address || '—' }}
              </div>
            </td>
            <!-- <td class="px-6 py-4">
               <div v-if="o.promotion_code !== 'None'" class="flex flex-col gap-0.5">
                  <span class="px-2 py-0.5 bg-green-50 text-green-600 rounded text-[10px] font-black border border-green-100 uppercase w-fit">{{ o.promotion_code }}</span>
                  <span class="text-[9px] text-slate-400 font-bold ml-1">{{ o.promotion_description }}</span>
               </div>
               <span v-else class="text-[10px] font-bold text-slate-400 italic">None</span>
            </td> -->
             <td class="px-6 py-4">
                <div class="max-w-[360px]">
                  <div class="flex items-center justify-between mb-1.5">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                      Items
                      <span class="ml-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                        {{ o.items.length }}
                      </span>
                    </div>
                    <button
                      v-if="o.items.length > 2"
                      type="button"
                      class="text-[10px] font-black text-indigo-700 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-full transition-all"
                      @click="toggleItems(o.id)"
                    >
                      {{ expandedItemsOrderId === o.id ? 'Less' : 'More' }}
                    </button>
                  </div>

                  <div class="space-y-1">
                    <div
                      v-for="it in (expandedItemsOrderId === o.id ? o.items : o.items.slice(0, 2))"
                      :key="it.id"
                      class="w-full flex items-center justify-between gap-4 px-4 py-2.5 rounded-2xl bg-emerald-50/50 hover:bg-emerald-100/60 transition-colors"
                    >
                      <div class="min-w-0 flex-1 flex flex-col items-end leading-tight">
                        <span class="font-mono text-[9px] px-1.5 py-0.5 rounded-full bg-white/80 text-slate-500 mb-0.5">
                          {{ getItemCode(it) }}
                        </span>
                        <div class="text-[11px] font-black text-slate-800 whitespace-nowrap w-full">
                          {{ it.product_name }}
                        </div>
                      </div>

                      <div class="shrink-0 flex items-center gap-1.5 text-[10px] font-black">
                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                          ${{ it.unit_price }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800">
                          × {{ it.qty }}
                        </span>
                      </div>
                    </div>

                    <div
                      v-if="expandedItemsOrderId !== o.id && o.items.length > 2"
                      class="text-[10px] font-black text-slate-500 pl-1"
                    >
                      +{{ o.items.length - 2 }} more item(s)
                    </div>
                  </div>
                </div>
             </td>
            <td class="px-6 py-4 text-right">
              <div class="text-[12px] font-black text-slate-700">${{ o.subtotal }}</div>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="text-[12px] font-black" :class="o.discount > 0 ? 'text-rose-600' : 'text-slate-400'">
                -${{ o.discount }}
              </div>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="text-sm font-black text-emerald-600">${{ o.total_amount }}</div>
            </td>

            <td class="px-6 py-4 text-center">
              <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase"
                :class="o.payment_method === 'ONLINE' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'">
                {{ o.payment_method || '—' }}
              </span>
            </td>
             
            <td class="px-6 py-4 text-center">
               <span :class="`bg-${o.status_color}-100 text-${o.status_color}-700`" class="px-3 py-1 rounded-full text-[10px] font-black uppercase">{{ o.order_status }}</span>
            </td>

            <td class="px-6 py-4 text-center">
               <span :class="`bg-${o.payment_color}-100 text-${o.payment_color}-700`" class="px-3 py-1 rounded-full text-[10px] font-black uppercase">{{ o.payment_status }}</span>
            </td>

            <td class="px-6 py-4 text-[11px] text-slate-400 font-mono">{{ o.created_at }}</td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2 text-right">
                <button @click="openViewModal(o)" class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-all shadow-sm" title="View Details">
                  <v-icon size="18">mdi-eye-outline</v-icon>
                </button>
                <button @click="deleteItem(o.id)" class="p-1.5 bg-rose-50 text-rose-500 rounded-lg hover:bg-rose-100 transition-all shadow-sm" title="Delete">
                  <v-icon size="18">mdi-trash-can-outline</v-icon>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        </table>
      </div>
      <div v-if="orders.links" class="p-3 flex justify-center gap-1">
         <Link v-for="(link, i) in orders.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'" />
      </div>
    </div>

    <!-- VIEW ORDER MODAL -->
    <Transition name="modal-fade">
      <div v-if="showViewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showViewModal = false"></div>
        <div class="relative bg-white w-full max-w-5xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-pop flex flex-col max-h-[90vh] min-h-0">
          <!-- Header -->
          <div class="px-8 py-6 flex justify-between items-center border-b border-slate-100">
            <div>
              <h2 class="text-xl font-black tracking-tight text-slate-800">Order Details #{{ viewOrder.order_number }}</h2>
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Placed on {{ viewOrder.created_at }}</p>
            </div>
            <button @click="showViewModal = false" class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center hover:bg-emerald-100 transition-all group">
              <v-icon color="emerald">mdi-close</v-icon>
            </button>
          </div>

          <div class="p-8 overflow-y-auto custom-scrollbar flex-1 min-h-0 bg-slate-50/30">
            <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <div class="rounded-3xl bg-white p-5 border border-slate-100 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Order No.</p>
                <p class="mt-2 text-lg font-black text-slate-900">#{{ viewOrder.order_number }}</p>
              </div>
              <div class="rounded-3xl bg-white p-5 border border-slate-100 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Order Status</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ viewOrder.order_status }}</p>
              </div>
              <div class="rounded-3xl bg-white p-5 border border-slate-100 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Payment</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ viewOrder.payment_status }}</p>
              </div>
              <div class="rounded-3xl bg-white p-5 border border-slate-100 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Method</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ viewOrder.payment_method || '—' }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <!-- Left: Customer & Status -->
              <div class="lg:col-span-1 space-y-6">
                <!-- Customer Card -->
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                  <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Customer Info</h3>
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold border border-emerald-100 overflow-hidden shrink-0 shadow-sm">
                      <img v-if="viewOrder.customer_image" :src="viewOrder.customer_image" class="w-full h-full object-cover">
                      <div v-else class="w-full h-full flex items-center justify-center bg-emerald-50 text-emerald-600">
                        {{ viewOrder.customer_name.charAt(0) }}
                      </div>
                    </div>
                    <div>
                      <div class="font-black text-slate-800">{{ viewOrder.customer_name }}</div>
                      <div class="text-[11px] text-slate-500 font-medium whitespace-nowrap overflow-hidden text-ellipsis max-w-[120px]">Verified Customer</div>
                    </div>
                  </div>
                </div>

                <!-- Status Management -->
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4">
                  <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Update Order Status</h3>
                    <div class="flex flex-wrap gap-2">
                      <button v-for="s in ['PENDING','COMPLETED','CANCELLED']" :key="s" 
                        @click="updateStatus(viewOrder, 'Order', s)"
                        :class="[
                          viewOrder.order_status === s ? 'ring-2 ring-emerald-500 bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-500 hover:bg-slate-100',
                          'px-3 py-2 rounded-xl text-[10px] font-black transition-all flex-1'
                        ]">
                        {{ s }}
                      </button>
                    </div>
                  </div>

                  <div class="pt-4 border-t border-slate-50">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Update Payment Status</h3>
                    <div class="flex flex-wrap gap-2">
                       <button v-for="s in ['PENDING','PAID','REFUNDED']" :key="s" 
                         @click="updateStatus(viewOrder, 'Payment', s)"
                         :class="[
                           viewOrder.payment_status === s ? 'ring-2 ring-emerald-500 bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-500 hover:bg-slate-100',
                           'px-3 py-2 rounded-xl text-[10px] font-black transition-all flex-1'
                         ]">
                         {{ s }}
                       </button>
                    </div>
                  </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4">
                  <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Delivery & Promo</h3>
                  <div>
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Address</p>
                    <p class="text-sm text-slate-700 leading-6">{{ viewOrder.customer_address || 'No address provided' }}</p>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                      <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Subtotal</p>
                      <p class="mt-1 text-sm font-black text-slate-900">${{ viewOrder.subtotal }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                      <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Discount</p>
                      <p class="mt-1 text-sm font-black text-rose-600">-${{ viewOrder.discount }}</p>
                    </div>
                  </div>
                  <div class="rounded-2xl bg-amber-50 p-4 border border-amber-100">
                    <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Promotion</p>
                    <p class="mt-1 text-sm font-black text-amber-800">{{ viewOrder.promotion_code }}</p>
                    <p v-if="viewOrder.promotion_description" class="mt-1 text-xs text-amber-700/80 leading-5">
                      {{ viewOrder.promotion_description }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Right: Order Items & Summary -->
              <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                  <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Order Items ({{ viewOrder.items.length }})</h3>
                  </div>
                  <div class="divide-y divide-slate-50">
                    <div v-for="item in viewOrder.items" :key="item.id" class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                      <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 overflow-hidden border border-slate-100 p-1 flex items-center justify-center">
                          <img v-if="item.image" :src="item.image" class="w-full h-full object-contain">
                          <v-icon v-else size="24" class="text-slate-200">mdi-package-variant</v-icon>
                        </div>
                        <div>
                          <div class="text-sm font-black text-slate-800">{{ item.product_name }}</div>
                          <div class="flex items-center gap-2 mt-0.5">
                            <div class="text-[11px] text-slate-500 font-bold">Qty: {{ item.qty }} × ${{ item.unit_price }}</div>
                            <div v-if="item.discount > 0" class="flex items-center gap-1.5">
                                <v-icon size="10" class="text-rose-500">mdi-tag-outline</v-icon>
                                <div class="px-1.5 py-0.5 bg-rose-50 text-rose-600 rounded text-[9px] font-black border border-rose-100 italic">
                                  -${{ item.discount }} Promotion Applied
                                </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="text-sm font-black text-slate-800">${{ item.line_total }}</div>
                    </div>
                  </div>

                  <!-- Summary Footer -->
                  <div class="p-6 bg-slate-50 border-t border-slate-100 space-y-3">
                    <div class="flex justify-between items-center text-xs font-bold text-slate-500">
                      <span>Subtotal</span>
                      <span>${{ viewOrder.subtotal }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-bold text-slate-500">
                       <div class="flex items-center gap-2">
                          <span>Discount</span>
                          <span v-if="viewOrder.promotion_code !== 'None'" class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[9px] uppercase tracking-tighter">{{ viewOrder.promotion_code }}</span>
                       </div>
                       <span class="text-rose-500">-${{ viewOrder.discount }}</span>
                    </div>
                    <div class="h-px bg-slate-200 my-2"></div>
                    <div class="flex justify-between items-center">
                      <span class="text-sm font-black text-slate-800 uppercase tracking-widest">Total Amount</span>
                      <span class="text-2xl font-black text-emerald-600">${{ viewOrder.total_amount }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Actions -->
          <div class="px-8 py-6 bg-white border-t border-slate-100 flex justify-end shrink-0">
             <button @click="showViewModal = false" class="px-10 py-3 bg-emerald-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-emerald-700 shadow-xl shadow-emerald-500/20 active:scale-95 transition-all">
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
@keyframes pop { 
  from { opacity: 0; transform: translateY(30px) scale(0.95); } 
  to { opacity: 1; transform: translateY(0) scale(1); } 
}
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
