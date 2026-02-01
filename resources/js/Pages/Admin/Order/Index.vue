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

// For expanding rows (optional feature) or showing details modal
const expandedOrderId = ref(null); 

const filteredOrders = computed(() => props.orders.data);

watch([search, from_date, to_date, statusFilter], ([newSearch, newFrom, newTo, newStatus]) => {
  router.get(route('admin.orders.index'), { 
    search: newSearch, from_date: newFrom, to_date: newTo, status: newStatus 
  }, { preserveState: true, replace: true });
});

function toggleItems(id) {
  expandedOrderId.value = expandedOrderId.value === id ? null : id;
}

function updateStatus(order, type, newStatus) {
  if(!confirm(`Change ${type} status to ${newStatus}?`)) return;
  
  router.put(route('admin.orders.update', order.id), {
     order_status: type === 'Order' ? newStatus : order.order_status,
     payment_status: type === 'Payment' ? newStatus : order.payment_status
  });
}

function deleteItem(id) {
  if(confirm("Delete this order? This cannot be undone.")) router.delete(route('admin.orders.destroy', id));
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
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            <th class="px-6 py-3">Order</th>
            <th class="px-6 py-3">Customer</th>
            <th class="px-6 py-3">Promotion</th>
            <th class="px-6 py-3 text-center">Items</th>
            <th class="px-6 py-3">Total</th>
            <th class="px-6 py-3 text-center">Order Status</th>
            <th class="px-6 py-3 text-center">Payment</th>
            <th class="px-6 py-3">Date</th>
            <th class="px-6 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <template v-for="o in filteredOrders" :key="o.id">
            <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer" @click="toggleItems(o.id)">
              <td class="px-6 py-4">
                 <div class="text-sm font-black text-slate-800">#{{ o.order_number }}</div>
              </td>
              <td class="px-6 py-4 text-xs font-bold text-slate-600">{{ o.customer_name }}</td>
              <td class="px-6 py-4">
                 <div v-if="o.promotion_code !== 'None'" class="flex flex-col gap-0.5">
                    <span class="px-2 py-0.5 bg-green-50 text-green-600 rounded text-[10px] font-black border border-green-100 uppercase w-fit">{{ o.promotion_code }}</span>
                    <span class="text-[9px] text-slate-400 font-bold ml-1">{{ o.promotion_description }}</span>
                 </div>
                 <span v-else class="text-[10px] font-bold text-slate-400 italic">None</span>
              </td>
              <td class="px-6 py-4 text-center">
                 <span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-bold text-slate-500">{{ o.items.length }} Items</span>
              </td>
              <td class="px-6 py-4 text-center">
                 <div class="text-sm font-black text-emerald-600">${{ o.total_amount }}</div>
                 <div v-if="o.discount > 0" class="text-[9px] text-rose-400 font-bold mt-0.5">-${{ o.discount }} save</div>
              </td>
              
              <!-- Status Dropdown (Mini) -->
              <td class="px-6 py-4 text-center">
                 <div class="relative group inline-block">
                    <span :class="`bg-${o.status_color}-100 text-${o.status_color}-700`" class="px-3 py-1 rounded-full text-[10px] font-black uppercase cursor-pointer">{{ o.order_status }}</span>
                    <!-- Hover Menu -->
                    <div class="absolute left-1/2 -translate-x-1/2 top-full mt-1 hidden group-hover:block bg-white border border-slate-100 shadow-xl rounded-lg z-50 p-1 min-w-[100px]">
                        <div v-for="s in ['PENDING','COMPLETED','CANCELLED']" :key="s" @click.stop="updateStatus(o, 'Order', s)" class="px-3 py-1.5 text-[10px] font-bold hover:bg-slate-50 rounded cursor-pointer text-slate-600">{{ s }}</div>
                    </div>
                 </div>
              </td>

              <!-- Payment Status -->
              <td class="px-6 py-4 text-center">
                 <div class="relative group inline-block">
                    <span :class="`bg-${o.payment_color}-100 text-${o.payment_color}-700`" class="px-3 py-1 rounded-full text-[10px] font-black uppercase cursor-pointer">{{ o.payment_status }}</span>
                     <div class="absolute left-1/2 -translate-x-1/2 top-full mt-1 hidden group-hover:block bg-white border border-slate-100 shadow-xl rounded-lg z-50 p-1 min-w-[100px]">
                        <div v-for="s in ['PENDING','PAID','REFUNDED']" :key="s" @click.stop="updateStatus(o, 'Payment', s)" class="px-3 py-1.5 text-[10px] font-bold hover:bg-slate-50 rounded cursor-pointer text-slate-600">{{ s }}</div>
                    </div>
                 </div>
              </td>

              <td class="px-6 py-4 text-[11px] text-slate-400 font-mono">{{ o.created_at }}</td>
              <td class="px-6 py-4 text-right">
                  <button @click.stop="deleteItem(o.id)" class="text-slate-300 hover:text-rose-500 transition-colors"><v-icon size="18">mdi-delete</v-icon></button>
              </td>
            </tr>
            
            <!-- Expanded Details Row -->
            <tr v-if="expandedOrderId === o.id" class="bg-slate-50/50">
               <td colspan="8" class="px-6 py-4">
                  <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
                      <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Order Items</h4>
                      <div class="space-y-2">
                          <div v-for="item in o.items" :key="item.id" class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-lg border border-transparent hover:border-slate-100 transition-all">
                              <div class="flex items-center gap-3">
                                  <div class="w-8 h-8 rounded bg-slate-100 overflow-hidden">
                                      <img v-if="item.image" :src="item.image" class="w-full h-full object-cover">
                                  </div>
                                  <div>
                                      <div class="text-xs font-bold text-slate-700">{{ item.product_name }}</div>
                                      <div class="text-[10px] text-slate-400">Qty: {{ item.qty }} × ${{ item.unit_price }}</div>
                                  </div>
                              </div>
                              <div class="text-xs font-bold text-slate-800">${{ item.line_total }}</div>
                          </div>
                      </div>
                      <div class="mt-4 pt-3 border-t border-slate-100 flex justify-end gap-6 text-xs">
                          <div v-if="o.promotion_description" class="mr-auto">
                              <span class="text-slate-400">Promo:</span>
                              <span class="ml-1 px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full font-bold text-[10px] uppercase">{{ o.promotion_description }}</span>
                          </div>
                          <div class="text-slate-500">Subtotal: <span class="font-bold text-slate-800">${{ o.subtotal }}</span></div>
                          <div class="text-slate-500">Discount: <span class="font-bold text-rose-500">-${{ o.discount }}</span></div>
                          <div class="text-emerald-600 font-black text-sm">Total: ${{ o.total_amount }}</div>
                      </div>
                  </div>
               </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div v-if="orders.links" class="p-3 flex justify-center gap-1">
         <Link v-for="(link, i) in orders.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'" />
      </div>
    </div>
  </AdminLayout>
</template>