<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    deliveries: Object,
    filters: Object,
    discountTypes: Array,
});

const search = ref(props.filters?.search || '');
const from_date = ref(props.filters?.from_date || '');
const to_date = ref(props.filters?.to_date || '');
const showModal = ref(false);
const isEdit = ref(false);
const showViewModal = ref(false);
const viewItem = ref(null);
const activeDropdown = ref(null);

const form = useForm({
    id: null,
    fee_amount_per: '',
    qty_kg: '',
    discount_type: '',
    discount_value: '',
});

const filteredDeliveries = computed(() => props.deliveries.data);

const previewTotal = computed(() => {
    const per = Number(form.fee_amount_per) || 0;
    const qty = Number(form.qty_kg) || 0;
    const subtotal = per * qty;
    const value = Number(form.discount_value) || 0;

    if (!form.discount_type) return subtotal;
    if (form.discount_type === 'free') return 0;
    if (form.discount_type === 'percentage') return Math.max(0, subtotal - subtotal * (value / 100));
    return Math.max(0, subtotal - value);
});

const previewSubtotal = computed(() => {
    return (Number(form.fee_amount_per) || 0) * (Number(form.qty_kg) || 0);
});

watch([search, from_date, to_date], ([newSearch, newFrom, newTo]) => {
    router.get(
        route('admin.deliveries.index'),
        { search: newSearch, from_date: newFrom, to_date: newTo },
        { preserveState: true, replace: true }
    );
});

function openAddModal() {
    isEdit.value = false;
    form.reset();
    form.discount_type = '';
    form.qty_kg = '';
    form.discount_value = '';
    showModal.value = true;
}

function openEditModal(item) {
    isEdit.value = true;
    form.id = item.id;
    form.fee_amount_per = item.fee_amount_per;
    form.qty_kg = item.qty_kg ?? '';
    form.discount_type = item.discount_type ?? '';
    form.discount_value = item.discount_value ?? '';
    showModal.value = true;
}

function openViewModal(item) {
    viewItem.value = item;
    showViewModal.value = true;
}

function save() {
    form.qty_kg = form.qty_kg === '' || form.qty_kg === null ? 0 : form.qty_kg;
    form.discount_type = form.discount_type ?? '';
    form.discount_value = form.discount_value === '' || form.discount_value === null ? 0 : form.discount_value;

    if (isEdit.value) {
        form.put(route('admin.deliveries.update', form.id), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('admin.deliveries.store'), {
            onSuccess: () => (showModal.value = false),
        });
    }
}

function deleteItem(id) {
    if (confirm('Delete this delivery fee?')) {
        router.delete(route('admin.deliveries.destroy', id), {
            onSuccess: () => (activeDropdown.value = null),
        });
    }
}

function toggleDropdown(id) {
    activeDropdown.value = activeDropdown.value === id ? null : id;
}

function discountLabel(item) {
    if (!item?.discount_type) return 'No discount';
    if (item.discount_type === 'free') return 'Free delivery';
    if (item.discount_type === 'percentage') return `${item.discount_value}% off`;
    return `$${Number(item.discount_value || 0).toFixed(2)} off`;
}

if (typeof window !== 'undefined') {
    window.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown-container')) {
            activeDropdown.value = null;
        }
    });
}
</script>

<template>
    <AdminLayout title="Delivery" subtitle="Fee per kg, quantity, and discounts">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-lg font-black text-slate-800 tracking-tight">Delivery Fees</h1>
            <button
                type="button"
                class="px-3 py-1.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-green-500/20 transition-all flex items-center gap-1.5 active:scale-95"
                @click="openAddModal"
            >
                <v-icon color="white" size="14">mdi-plus</v-icon>
                New Delivery
            </button>
        </div>

        <div class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm backdrop-blur-xl">
            <div class="flex flex-col md:flex-row items-center gap-2">
                <div class="relative w-full md:w-1/3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search discount type..."
                        class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-sm focus:ring-4 focus:ring-green-500/10 transition-all placeholder:text-slate-400 font-medium"
                    />
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center bg-green-500 rounded-lg shadow-sm">
                        <v-icon color="white" size="14">mdi-magnify</v-icon>
                    </div>
                </div>
                <div class="flex items-center gap-2 p-1 bg-slate-50/50 rounded-[1.5rem] w-full md:w-auto">
                    <div class="flex items-center gap-2 px-3">
                        <v-icon size="18" class="text-slate-400">mdi-calendar-range</v-icon>
                    </div>
                    <div class="flex items-center gap-1.5 bg-white p-1 rounded-2xl shadow-sm border border-slate-100">
                        <input v-model="from_date" type="date" class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer" />
                        <div class="w-[1px] h-4 bg-slate-200"></div>
                        <input v-model="to_date" type="date" class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer" />
                    </div>
                    <button
                        v-if="search || from_date || to_date"
                        type="button"
                        class="ml-2 mr-2 w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 active:scale-90 transition-all"
                        @click="search = ''; from_date = ''; to_date = ''"
                    >
                        <v-icon size="16">mdi-filter-off</v-icon>
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Fee / kg</th>
                        <th class="px-6 py-3">Qty (kg)</th>
                        <th class="px-6 py-3">Subtotal</th>
                        <th class="px-6 py-3">Discount</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="item in filteredDeliveries" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-sm font-bold text-slate-700">${{ Number(item.fee_amount_per).toFixed(2) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ Number(item.qty_kg).toFixed(2) }} kg</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${{ Number(item.subtotal).toFixed(2) }}</td>
                        <td class="px-6 py-4 text-xs font-bold text-amber-600 capitalize">{{ discountLabel(item) }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-green-600">${{ Number(item.total).toFixed(2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end relative dropdown-container">
                                <button
                                    type="button"
                                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all active:scale-90"
                                    @click.stop="toggleDropdown(item.id)"
                                >
                                    <v-icon size="20">mdi-dots-vertical</v-icon>
                                </button>
                                <div
                                    v-if="activeDropdown === item.id"
                                    class="absolute right-10 top-0 z-50 w-44 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 py-2"
                                >
                                    <button
                                        type="button"
                                        class="w-full px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 text-xs font-bold"
                                        @click="openViewModal(item); activeDropdown = null"
                                    >
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <v-icon size="16">mdi-eye-outline</v-icon>
                                        </div>
                                        View Detail
                                    </button>
                                    <button
                                        type="button"
                                        class="w-full px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 text-xs font-bold border-t border-slate-50"
                                        @click="openEditModal(item); activeDropdown = null"
                                    >
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <v-icon size="16">mdi-pencil-outline</v-icon>
                                        </div>
                                        Edit Delivery
                                    </button>
                                    <button
                                        type="button"
                                        class="w-full px-4 py-2.5 flex items-center gap-3 text-rose-500 hover:bg-rose-50 text-xs font-bold border-t border-slate-50"
                                        @click="deleteItem(item.id)"
                                    >
                                        <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
                                            <v-icon size="16">mdi-trash-can-outline</v-icon>
                                        </div>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!filteredDeliveries?.length">
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No delivery fees yet.</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="deliveries.links" class="p-3 flex justify-center gap-1">
                <Link
                    v-for="(link, i) in deliveries.links"
                    :key="i"
                    :href="link.url || '#'"
                    class="px-3 py-1 text-xs rounded"
                    :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500'"
                    v-html="link.label"
                />
            </div>
        </div>

        <Transition name="modal-fade">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showModal = false"></div>
                <div class="relative bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden animate-pop flex flex-col max-h-[90vh]">
                    <div class="px-8 py-6 shrink-0 flex items-center justify-between border-b border-slate-100">
                        <h2 class="text-xl font-black tracking-tight text-slate-800">
                            {{ isEdit ? 'Edit Delivery Fee' : 'New Delivery Fee' }}
                        </h2>
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100" @click="showModal = false">
                            <v-icon size="20" class="text-slate-400">mdi-close</v-icon>
                        </button>
                    </div>
                    <div class="p-8 space-y-4 overflow-y-auto">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Fee amount / kg ($)</label>
                                <input v-model="form.fee_amount_per" type="number" step="0.01" min="0" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none" />
                                <div v-if="form.errors.fee_amount_per" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.fee_amount_per }}</div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Quantity (kg) <span class="normal-case text-slate-300">optional</span></label>
                                <input v-model="form.qty_kg" type="number" step="0.001" min="0" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none" />
                                <div v-if="form.errors.qty_kg" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.qty_kg }}</div>
                            </div>
                        </div>
                        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 text-sm">
                            <span class="text-slate-500">Subtotal:</span>
                            <span class="font-bold text-slate-800 ml-2">${{ previewSubtotal.toFixed(2) }}</span>
                            <span class="text-slate-400 mx-2">→</span>
                            <span class="text-slate-500">Total:</span>
                            <span class="font-black text-emerald-600 ml-2">${{ previewTotal.toFixed(2) }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Discount type <span class="normal-case text-slate-300">optional</span></label>
                                <select v-model="form.discount_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none capitalize">
                                    <option value="">No discount</option>
                                    <option v-for="t in discountTypes" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Discount value <span class="normal-case text-slate-300">optional</span></label>
                                <input v-model="form.discount_value" type="number" step="0.01" min="0" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none" />
                            </div>
                        </div>
                    </div>
                    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                        <button type="button" class="px-6 py-2.5 text-xs font-bold text-slate-400 uppercase tracking-widest" @click="showModal = false">Cancel</button>
                        <button
                            type="button"
                            class="px-8 py-3 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-black rounded-2xl shadow-lg uppercase tracking-widest flex items-center gap-2"
                            :disabled="form.processing"
                            @click="save"
                        >
                            <v-icon v-if="form.processing" size="14" class="animate-spin">mdi-loading</v-icon>
                            {{ isEdit ? 'Save Changes' : 'Create Delivery' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="modal-fade">
            <div v-if="showViewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showViewModal = false"></div>
                <div class="relative bg-white w-full max-w-3xl rounded-3xl shadow-2xl overflow-hidden animate-pop flex flex-col">
                    <div class="px-8 py-6 flex items-center justify-between border-b border-slate-100">
                        <h2 class="text-xl font-black text-slate-800">Delivery Details</h2>
                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 hover:bg-emerald-100" @click="showViewModal = false">
                            <v-icon size="20" class="text-emerald-600">mdi-close</v-icon>
                        </button>
                    </div>
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Fee per kg</label>
                                <span class="text-2xl font-black text-slate-800">${{ Number(viewItem?.fee_amount_per).toFixed(2) }}</span>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Quantity</label>
                                <span class="text-lg font-bold text-slate-700">{{ Number(viewItem?.qty_kg || 0).toFixed(2) }} kg</span>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Subtotal</label>
                                <span class="text-lg font-bold text-slate-600">${{ Number(viewItem?.subtotal).toFixed(2) }}</span>
                            </div>
                        </div>
                        <div class="p-6 bg-emerald-50 rounded-2xl border border-emerald-100 space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Discount</label>
                                <span class="text-sm font-bold text-amber-700 capitalize">{{ discountLabel(viewItem) }}</span>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Final total</label>
                                <span class="text-3xl font-black text-emerald-700">${{ Number(viewItem?.total).toFixed(2) }}</span>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Created</label>
                                <span class="text-sm text-slate-600">{{ viewItem?.created_at }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-8 py-6 bg-slate-50 border-t flex justify-end gap-3">
                        <button type="button" class="px-6 py-2.5 text-xs font-bold text-slate-500" @click="openEditModal(viewItem); showViewModal = false">Edit</button>
                        <button type="button" class="px-8 py-3 bg-emerald-600 text-white text-xs font-black rounded-2xl uppercase tracking-widest" @click="showViewModal = false">Close</button>
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
