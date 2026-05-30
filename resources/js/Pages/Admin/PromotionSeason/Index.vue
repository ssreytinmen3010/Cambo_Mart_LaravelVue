<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    seasons: Object,
    filters: Object,
    statuses: Array,
});

const search = ref(props.filters?.search || '');
const from_date = ref(props.filters?.from_date || '');
const to_date = ref(props.filters?.to_date || '');
const showModal = ref(false);
const isEdit = ref(false);
const showViewModal = ref(false);
const viewItem = ref(null);
const activeDropdown = ref(null);
const fileInput = ref(null);
const previewImage = ref(null);

const form = useForm({
    id: null,
    image: '',
    code: '',
    start_date: '',
    end_date: '',
    promotion_type: '',
    promotion_value: '',
    status: 'DRAFT',
});

const promotionTypeOptions = [
    { value: 'FIX', label: 'Fix' },
    { value: 'PERCENTAGE', label: 'Percentage' },
    { value: 'FREE_DELIVERY', label: 'Free Delivery' },
];

const filteredSeasons = computed(() => props.seasons.data);

watch([search, from_date, to_date], ([newSearch, newFrom, newTo]) => {
    router.get(
        route('admin.promotion-seasons.index'),
        { search: newSearch, from_date: newFrom, to_date: newTo },
        { preserveState: true, replace: true }
    );
});

function openAddModal() {
    isEdit.value = false;
    form.reset();
    form.status = 'DRAFT';
    previewImage.value = null;
    showModal.value = true;
}

function openEditModal(item) {
    isEdit.value = true;
    form.id = item.id;
    form.image = item.image || '';
    form.code = item.code;
    form.start_date = item.start_date || '';
    form.end_date = item.end_date || '';
    form.promotion_type = item.promotion_type || '';
    form.promotion_value = item.promotion_value || '';
    form.status = item.status;
    previewImage.value = item.image || null;
    showModal.value = true;
}

function openViewModal(item) {
    viewItem.value = item;
    showViewModal.value = true;
}

async function handleImageUpload(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    previewImage.value = URL.createObjectURL(file);
    const formData = new FormData();
    formData.append('image', file);

    try {
        const axios = (await import('axios')).default;
        const res = await axios.post('/upload-image', formData);
        form.image = res.data.url;
    } catch {
        alert('Upload failed');
    }
}

function clearImage() {
    previewImage.value = null;
    form.image = '';
    if (fileInput.value) fileInput.value.value = '';
}

function save() {
    if (isEdit.value) {
        form.put(route('admin.promotion-seasons.update', form.id), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('admin.promotion-seasons.store'), {
            onSuccess: () => (showModal.value = false),
        });
    }
}

function deleteItem(id) {
    if (confirm('Delete this promotion season?')) {
        router.delete(route('admin.promotion-seasons.destroy', id), {
            onSuccess: () => (activeDropdown.value = null),
        });
    }
}

function toggleDropdown(id) {
    activeDropdown.value = activeDropdown.value === id ? null : id;
}

function statusBadgeClass(status) {
    return {
        ACTIVE: 'bg-green-100 text-green-700',
        DRAFT: 'bg-gray-100 text-gray-500',
        PAUSE: 'bg-yellow-50 text-yellow-600',
        EXPIRE: 'bg-rose-100 text-rose-600',
    }[status] || 'bg-gray-100 text-gray-500';
}

function promotionTypeLabel(type) {
    return {
        FIX: 'Fix',
        PERCENTAGE: 'Percentage',
        FREE_DELIVERY: 'Free Delivery',
    }[type] || type || '—';
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
    <AdminLayout title="Promotion Season" subtitle="Seasonal coupons and campaigns">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-lg font-black text-slate-800 tracking-tight">Promotion Season</h1>
            <button
                type="button"
                class="px-3 py-1.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-green-500/20 transition-all flex items-center gap-1.5 active:scale-95"
                @click="openAddModal"
            >
                <v-icon color="white" size="14">mdi-plus</v-icon>
                New Season
            </button>
        </div>

        <div class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm backdrop-blur-xl">
            <div class="flex flex-col md:flex-row items-center gap-2">
                <div class="relative w-full md:w-1/3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search coupon code..."
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
                        <th class="px-6 py-3">Code</th>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Promotion</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="item in filteredSeasons" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-mono font-bold text-slate-700 bg-slate-100 px-2.5 py-1.5 rounded inline-block">
                                {{ item.code }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-16 h-16">
                                <img v-if="item.image" :src="item.image" :alt="item.code" class="w-16 h-16 object-cover rounded-lg border border-slate-200" />
                                <div v-else class="w-16 h-16 rounded-lg border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-slate-300">
                                    <v-icon size="22">mdi-image-off-outline</v-icon>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <div class="font-bold text-slate-700">{{ promotionTypeLabel(item.promotion_type) }}</div>
                            <div class="text-xs text-slate-400">${{ Number(item.promotion_value || 0).toFixed(2) }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            <div v-if="item.start_date">{{ item.start_date }} <span class="text-slate-300">to</span> {{ item.end_date }}</div>
                            <div v-else class="italic">No end date</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <div v-if="item.status === 'ACTIVE'" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span :class="statusBadgeClass(item.status)" class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-tighter">
                                    {{ item.status }}
                                </span>
                            </div>
                        </td>
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
                                    class="absolute right-10 top-0 z-50 w-44 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 py-2 animate-in fade-in slide-in-from-right-2 duration-200"
                                >
                                    <button
                                        type="button"
                                        class="w-full px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors text-xs font-bold"
                                        @click="openViewModal(item); activeDropdown = null"
                                    >
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <v-icon size="16">mdi-eye-outline</v-icon>
                                        </div>
                                        View Detail
                                    </button>
                                    <button
                                        type="button"
                                        class="w-full px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors text-xs font-bold border-t border-slate-50"
                                        @click="openEditModal(item); activeDropdown = null"
                                    >
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <v-icon size="16">mdi-pencil-outline</v-icon>
                                        </div>
                                        Edit Season
                                    </button>
                                    <button
                                        type="button"
                                        class="w-full px-4 py-2.5 flex items-center gap-3 text-rose-500 hover:bg-rose-50 transition-colors text-xs font-bold border-t border-slate-50"
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
                    <tr v-if="!filteredSeasons?.length">
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No promotion seasons yet.</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="seasons.links" class="p-3 flex justify-center gap-1">
                <Link
                    v-for="(link, i) in seasons.links"
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
                            {{ isEdit ? 'Edit Promotion Season' : 'New Promotion Season' }}
                        </h2>
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100" @click="showModal = false">
                            <v-icon size="20" class="text-slate-400">mdi-close</v-icon>
                        </button>
                    </div>
                    <div class="p-8 space-y-4 overflow-y-auto custom-scrollbar">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Coupon Code</label>
                            <input v-model="form.code" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none" />
                            <div v-if="form.errors.code" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.code }}</div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Image</label>
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
                                    <img v-if="previewImage" :src="previewImage" alt="" class="w-full h-full object-cover" />
                                    <v-icon v-else size="26" class="text-slate-300">mdi-image-outline</v-icon>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
                                    <button type="button" class="px-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-black text-slate-600 hover:bg-slate-50" @click="fileInput?.click()">Upload</button>
                                    <button v-if="previewImage" type="button" class="px-4 py-2.5 bg-rose-50 border border-rose-100 rounded-2xl text-xs font-black text-rose-600" @click="clearImage">Remove</button>
                                </div>
                            </div>
                            <div v-if="form.errors.image" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.image }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Start Date</label>
                                <input v-model="form.start_date" type="date" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">End Date</label>
                                <input v-model="form.end_date" type="date" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Promotion Type</label>
                                <select v-model="form.promotion_type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                                    <option value="">Select promotion type</option>
                                    <option v-for="option in promotionTypeOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <div v-if="form.errors.promotion_type" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.promotion_type }}</div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Promotion Value</label>
                                <input v-model="form.promotion_value" type="number" step="0.01" min="0" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none" />
                                <div v-if="form.errors.promotion_value" class="text-rose-500 text-[10px] ml-2 font-bold">{{ form.errors.promotion_value }}</div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Status</label>
                            <select v-model="form.status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                        <button type="button" class="px-6 py-2.5 text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest" @click="showModal = false">Cancel</button>
                        <button
                            type="button"
                            class="px-8 py-3 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-black rounded-2xl shadow-lg shadow-green-500/20 uppercase tracking-widest flex items-center gap-2"
                            :disabled="form.processing"
                            @click="save"
                        >
                            <v-icon v-if="form.processing" size="14" class="animate-spin">mdi-loading</v-icon>
                            {{ isEdit ? 'Save Changes' : 'Create Season' }}
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
                        <h2 class="text-xl font-black text-slate-800">Promotion Season Details</h2>
                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 hover:bg-emerald-100" @click="showViewModal = false">
                            <v-icon size="20" class="text-emerald-600">mdi-close</v-icon>
                        </button>
                    </div>
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl border overflow-hidden bg-slate-50 flex items-center justify-center">
                                    <img v-if="viewItem?.image" :src="viewItem.image" alt="" class="w-full h-full object-cover" />
                                    <v-icon v-else size="32" class="text-emerald-600">mdi-calendar-star</v-icon>
                                </div>
                                <div>
                                    <div class="text-xs font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded inline-block mb-1">{{ viewItem?.code }}</div>
                                    <p class="text-sm text-slate-500">Created {{ viewItem?.created_at }}</p>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Status</label>
                                <span :class="statusBadgeClass(viewItem?.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase">{{ viewItem?.status }}</span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Start Date</label>
                                <span class="text-sm font-bold text-slate-700">{{ viewItem?.start_date || '—' }}</span>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">End Date</label>
                                <span class="text-sm font-bold text-slate-700">{{ viewItem?.end_date || '—' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-8 py-6 bg-slate-50 border-t flex justify-end">
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
