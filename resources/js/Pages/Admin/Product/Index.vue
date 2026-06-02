<script setup>
import { ref, computed, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, router, Link } from "@inertiajs/vue3";

const props = defineProps({
    products: Object,
    categories: Array,
    brands: Array,
    statusStocks: Array, // Added for dynamic constants
    filters: Object,
});

// State
const search = ref(props.filters?.search || "");
const from_date = ref(props.filters?.from_date || "");
const to_date = ref(props.filters?.to_date || "");
const showModal = ref(false);
const showViewModal = ref(false);
const viewProduct = ref(null);
const isEdit = ref(false);
const fileInput = ref(null);
const previewImage = ref(null);
const imageUploadError = ref(null);

const activeDropdown = ref(null);

function toggleDropdown(id) {
    if (activeDropdown.value === id) {
        activeDropdown.value = null;
    } else {
        activeDropdown.value = id;
    }
}

// Close dropdown when clicking outside
if (typeof window !== "undefined") {
    window.addEventListener("click", (e) => {
        if (!e.target.closest(".dropdown-container")) {
            activeDropdown.value = null;
        }
    });
}

const form = useForm({
    id: null,
    name: "",
    product_code: "",
    price: "",
    stock: "",
    category_id: "",
    brand_id: "",
    description: "",
    image: "",
    status: true,
    status_stock:
        props.statusStocks && props.statusStocks.length > 0
            ? props.statusStocks[0].id
            : "Sale",
});

const filteredProducts = computed(() => {
    return props.products.data;
});

function categoryBelongsToBrand(category, brandId) {
    if (!brandId) return false;
    const brandIds = Array.isArray(category.brand_ids)
        ? category.brand_ids
        : [];
    if (brandIds.length > 0) {
        return brandIds.some((id) => id == brandId);
    }
    return category.brand_id == brandId;
}

const filteredCategories = computed(() => {
    if (!form.brand_id) return [];
    return props.categories.filter((c) =>
        categoryBelongsToBrand(c, form.brand_id),
    );
});

// Watch brand change to filter categories
watch(
    () => form.brand_id,
    (newBrandId) => {
        if (!isEdit.value) {
            form.category_id = "";
        } else {
            const belongs = props.categories.find(
                (c) =>
                    c.id == form.category_id &&
                    categoryBelongsToBrand(c, newBrandId),
            );
            if (!belongs) form.category_id = "";
        }
    },
);

// Watch for search/date changes to trigger server-side filtering
watch([search, from_date, to_date], ([newSearch, newFrom, newTo]) => {
    router.get(
        route("admin.products.index"),
        {
            search: newSearch,
            from_date: newFrom,
            to_date: newTo,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

// Auto-toggle status_stock based on stock count
watch(
    () => form.stock,
    (newStock) => {
        if (
            newStock !== "" &&
            newStock !== null &&
            typeof newStock !== "undefined"
        ) {
            const stockNum = Number(newStock);
            if (stockNum <= 0) {
                form.status_stock = "Sale Out";
            } else {
                form.status_stock = "Sale";
            }
        }
    },
    { immediate: true },
);

// Actions
function openAddModal() {
    isEdit.value = false;
    form.reset();
    form.status = true;
    form.status_stock =
        props.statusStocks && props.statusStocks.length > 0
            ? props.statusStocks[0].id
            : "Sale";
    previewImage.value = null;
    showModal.value = true;
}

function openEditModal(prod) {
    activeDropdown.value = null;
    isEdit.value = true;
    form.id = prod.id;
    form.name = prod.name;
    form.product_code = prod.code;
    form.price = prod.price;
    form.stock = prod.stock;
    form.brand_id = prod.brand_id || "";
    form.category_id = prod.category_id || "";

    form.image = prod.image;
    form.status = prod.is_active;
    // Let the watcher handle status_stock based on the stock we just set
    previewImage.value = prod.image;
    showModal.value = true;
}

function openViewModal(prod) {
    activeDropdown.value = null;
    viewProduct.value = prod;
    showViewModal.value = true;
}

async function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    previewImage.value = URL.createObjectURL(file);
    const formData = new FormData();
    formData.append("image", file);
    try {
        const axios = (await import("axios")).default;
        const res = await axios.post("/upload-image", formData);
        form.image = res.data.url;
    } catch (error) {
        alert("Upload failed");
    }
}

function save() {
    const routeName = isEdit.value
        ? "admin.products.update"
        : "admin.products.store";

    if (isEdit.value) {
        form.put(route(routeName, form.id), {
            onSuccess: () => (showModal.value = false),
            onError: (errors) => console.log(errors),
        });
    } else {
        form.post(route(routeName), {
            onSuccess: () => (showModal.value = false),
            onError: (errors) => console.log(errors),
        });
    }
}

function deleteItem(id) {
    activeDropdown.value = null;
    if (confirm("Delete product?"))
        router.delete(route("admin.products.destroy", id));
}
</script>

<template>
    <AdminLayout title="Products" subtitle="Manage inventory">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-lg font-black text-slate-800 tracking-tight">
                Product Inventory
            </h1>
            <div class="flex items-center gap-2">
                <Link
                    href="/admin/brands"
                    class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-lg shadow-sm hover:bg-slate-50 transition-all flex items-center gap-1.5"
                >
                    <v-icon size="14">mdi-tag-outline</v-icon> Add Brand
                </Link>
                <Link
                    href="/admin/categories"
                    class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-lg shadow-sm hover:bg-slate-50 transition-all flex items-center gap-1.5"
                >
                    <v-icon size="14">mdi-shape-outline</v-icon> Add Category
                </Link>
                <button
                    @click="openAddModal"
                    class="px-3 py-1.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-green-500/20 transition-all flex items-center gap-1.5 active:scale-95"
                >
                    <v-icon color="white" size="14">mdi-plus</v-icon> New
                    Product
                </button>
            </div>
        </div>

        <!-- Deep Filter Header -->
        <div
            class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm backdrop-blur-xl"
        >
            <div class="flex flex-col md:flex-row items-center gap-2">
                <!-- Search Section -->
                <div class="relative w-full md:w-1/3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search name, code, sku..."
                        class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-sm focus:ring-4 focus:ring-green-500/10 transition-all placeholder:text-slate-400 font-medium"
                    />
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center bg-green-500 rounded-lg shadow-sm"
                    >
                        <v-icon color="white" size="14">mdi-magnify</v-icon>
                    </div>
                </div>

                <!-- Date Range section -->
                <div
                    class="flex items-center gap-2 p-1 bg-slate-50/50 rounded-[1.5rem] w-full md:w-auto"
                >
                    <div class="flex items-center gap-2 px-3">
                        <v-icon size="18" class="text-slate-400"
                            >mdi-calendar-range</v-icon
                        >
                        <span
                            class="text-[10px] font-black text-slate-400 uppercase tracking-tighter"
                            >Date Range</span
                        >
                    </div>

                    <div
                        class="flex items-center gap-1.5 bg-white p-1 rounded-2xl shadow-sm border border-slate-100"
                    >
                        <input
                            v-model="from_date"
                            type="date"
                            class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer"
                        />
                        <div class="w-[1px] h-4 bg-slate-200"></div>
                        <input
                            v-model="to_date"
                            type="date"
                            class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer"
                        />
                    </div>

                    <!-- Clear Button -->
                    <button
                        v-if="search || from_date || to_date"
                        @click="
                            search = '';
                            from_date = '';
                            to_date = '';
                        "
                        class="ml-2 mr-2 w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 active:scale-90 transition-all"
                        title="Reset Filters"
                    >
                        <v-icon size="16">mdi-filter-off</v-icon>
                    </button>
                </div>

                <div class="flex-1"></div>

                <!-- Meta info -->
                <div
                    class="hidden lg:flex items-center px-6 text-[11px] font-bold text-slate-400 uppercase tracking-widest gap-2"
                >
                    <span
                        class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"
                    ></span>
                    {{ products.total }} Products Found
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr
                        class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
                    >
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Code</th>
                        <th class="px-6 py-3">Brand / Category</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Stock</th>
                        <th class="px-6 py-3">Availability</th>
                        <th class="px-6 py-3">Created</th>
                        <th class="px-6 py-3">Updated</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr
                        v-for="p in filteredProducts"
                        :key="p.id"
                        class="hover:bg-slate-50/50 transition-colors"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100"
                                >
                                    <img
                                        v-if="p.image"
                                        :src="p.image"
                                        class="w-full h-full object-cover"
                                    />
                                    <v-icon
                                        v-else
                                        size="18"
                                        class="text-gray-300"
                                        >mdi-package-variant</v-icon
                                    >
                                </div>
                                <div class="text-sm font-bold text-slate-700">
                                    {{ p.name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-500">
                            {{ p.code }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="font-bold text-slate-600">
                                {{ p.brand }}
                            </div>
                            <div class="text-slate-400">{{ p.category }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-green-600">
                            ${{ p.price }}
                        </td>
                        <td
                            class="px-6 py-4 text-sm font-bold"
                            :class="
                                p.stock > 0 ? 'text-slate-600' : 'text-red-500'
                            "
                        >
                            {{ p.stock }}
                        </td>
                        <td class="px-6 py-4">
                            <span
                                :class="
                                    p.status_stock === 'Sale'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-amber-100 text-amber-700'
                                "
                                class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide"
                            >
                                {{ p.status_stock }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 text-[11px] text-slate-500 font-medium"
                        >
                            {{ p.created_at }}
                        </td>
                        <td
                            class="px-6 py-4 text-[11px] text-slate-500 font-medium"
                        >
                            {{ p.updated_at }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                :class="
                                    p.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-50 text-red-500'
                                "
                                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter"
                                >{{ p.status }}</span
                            >
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div
                                class="flex justify-end relative dropdown-container"
                            >
                                <button
                                    @click.stop="toggleDropdown(p.id)"
                                    class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-all"
                                >
                                    <v-icon size="20">mdi-dots-vertical</v-icon>
                                </button>

                                <div
                                    v-if="activeDropdown === p.id"
                                    class="absolute right-0 mt-10 w-40 bg-white rounded-xl shadow-xl border border-slate-100 z-50 py-1 overflow-hidden animate-pop"
                                >
                                    <button
                                        @click="openViewModal(p)"
                                        class="w-full px-4 py-2.5 text-left text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2"
                                    >
                                        <v-icon size="16" class="text-blue-500"
                                            >mdi-eye-outline</v-icon
                                        >
                                        View Detail
                                    </button>
                                    <button
                                        @click="openEditModal(p)"
                                        class="w-full px-4 py-2.5 text-left text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2"
                                    >
                                        <v-icon
                                            size="16"
                                            class="text-emerald-500"
                                            >mdi-pencil-outline</v-icon
                                        >
                                        Edit Product
                                    </button>
                                    <div
                                        class="h-[1px] bg-slate-100 my-1"
                                    ></div>
                                    <button
                                        @click="deleteItem(p.id)"
                                        class="w-full px-4 py-2.5 text-left text-xs font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-2"
                                    >
                                        <v-icon size="16"
                                            >mdi-trash-can-outline</v-icon
                                        >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="products.links" class="p-3 flex justify-center gap-1">
                <Link
                    v-for="(link, i) in products.links"
                    :key="i"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-3 py-1 text-xs rounded"
                    :class="
                        link.active
                            ? 'bg-green-500 text-white'
                            : 'text-gray-500'
                    "
                />
            </div>
        </div>

        <!-- VIEW MODAL -->
        <Transition name="modal-fade">
            <div
                v-if="showViewModal"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-900/40 backdrop-blur-md"
                    @click="showViewModal = false"
                ></div>
                <div
                    class="relative bg-white w-full max-w-5xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-pop flex flex-col max-h-[90vh]"
                >
                    <div
                        class="bg-gradient-to-tr from-blue-600 to-indigo-500 px-8 py-6 text-white shrink-0 flex justify-between items-center"
                    >
                        <h2 class="text-xl font-black tracking-tight">
                            Product Details
                        </h2>
                        <button
                            @click="showViewModal = false"
                            class="hover:rotate-90 transition-all"
                        >
                            <v-icon color="white">mdi-close</v-icon>
                        </button>
                    </div>

                    <div class="p-8 overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left side: Image and status -->
                            <div class="space-y-6">
                                <div
                                    class="aspect-square bg-slate-50 rounded-3xl border border-slate-100 flex items-center justify-center overflow-hidden shadow-inner"
                                >
                                    <img
                                        v-if="viewProduct.image"
                                        :src="viewProduct.image"
                                        class="w-full h-full object-contain p-4"
                                    />
                                    <v-icon
                                        v-else
                                        size="100"
                                        class="text-slate-200"
                                        >mdi-package-variant</v-icon
                                    >
                                </div>

                                <div
                                    class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100"
                                >
                                    <div
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Current Status
                                    </div>
                                    <span
                                        :class="
                                            viewProduct.is_active
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-50 text-red-500'
                                        "
                                        class="px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-tighter shadow-sm"
                                    >
                                        {{ viewProduct.status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Right side: Info fields -->
                            <div class="space-y-5">
                                <div>
                                    <label
                                        class="text-[10px] font-bold text-slate-400 uppercase block mb-1"
                                        >Product Name</label
                                    >
                                    <p
                                        class="text-lg font-black text-slate-800 leading-tight"
                                    >
                                        {{ viewProduct.name }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase block mb-1"
                                            >Code / SKU</label
                                        >
                                        <p
                                            class="text-sm font-bold text-slate-600 font-mono"
                                        >
                                            {{ viewProduct.code }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase block mb-1"
                                            >Price</label
                                        >
                                        <p
                                            class="text-lg font-black text-green-600"
                                        >
                                            ${{ viewProduct.price }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase block mb-1"
                                            >Brand</label
                                        >
                                        <span
                                            class="px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold"
                                            >{{ viewProduct.brand }}</span
                                        >
                                    </div>
                                    <div>
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase block mb-1"
                                            >Availability</label
                                        >
                                        <span
                                            :class="
                                                viewProduct.status_stock ===
                                                'Sale'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-amber-100 text-amber-700'
                                            "
                                            class="px-3 py-1 rounded-lg text-xs font-bold"
                                        >
                                            {{ viewProduct.status_stock }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="text-[10px] font-bold text-slate-400 uppercase block mb-1"
                                        >Stock Availability</label
                                    >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden"
                                        >
                                            <div
                                                class="h-full bg-blue-500 rounded-full"
                                                :style="{
                                                    width:
                                                        Math.min(
                                                            viewProduct.stock,
                                                            100,
                                                        ) + '%',
                                                }"
                                            ></div>
                                        </div>
                                        <span
                                            class="text-sm font-black text-slate-700"
                                            >{{ viewProduct.stock }} units</span
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="text-[10px] font-bold text-slate-400 uppercase block mb-1"
                                        >Description</label
                                    >
                                    <div
                                        class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-sm text-slate-600 font-medium whitespace-pre-line min-h-[100px]"
                                    >
                                        {{
                                            viewProduct.description ||
                                            "No description provided."
                                        }}
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100"
                                >
                                    <div>
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase block mb-0.5"
                                            >Created At</label
                                        >
                                        <p
                                            class="text-[11px] font-bold text-slate-500"
                                        >
                                            {{ viewProduct.created_at }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase block mb-0.5"
                                            >Last Updated</label
                                        >
                                        <p
                                            class="text-[11px] font-bold text-slate-500"
                                        >
                                            {{ viewProduct.updated_at }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end shrink-0"
                    >
                        <button
                            @click="showViewModal = false"
                            class="px-8 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 shadow-lg active:scale-95 transition-all"
                        >
                            Close Details
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- MODAL -->
        <Transition name="modal-fade">
            <div
                v-if="showModal"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-900/40 backdrop-blur-md"
                    @click="showModal = false"
                ></div>
                <div
                    class="relative bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden animate-pop flex flex-col max-h-[90vh]"
                >
                    <div
                        class="bg-white px-8 py-6 border-b border-slate-100 flex justify-between items-center shrink-0"
                    >
                        <div class="text-left">
                            <h2
                                class="text-xl font-black text-slate-800 tracking-tight"
                            >
                                {{ isEdit ? "Edit Product" : "New Product" }}
                            </h2>
                        </div>
                        <button
                            @click="showModal = false"
                            class="text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <v-icon size="24">mdi-close</v-icon>
                        </button>
                    </div>

                    <div class="p-8 space-y-4 overflow-y-auto custom-scrollbar">
                        <!-- Image -->
                        <div class="flex justify-center mb-4">
                            <div
                                class="relative w-24 h-24 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden cursor-pointer"
                                @click="$refs.fileInput.click()"
                            >
                                <img
                                    v-if="previewImage"
                                    :src="previewImage"
                                    class="w-full h-full object-cover"
                                />
                                <v-icon v-else class="text-slate-300"
                                    >mdi-camera-plus</v-icon
                                >
                            </div>
                            <input
                                type="file"
                                ref="fileInput"
                                class="hidden"
                                accept="image/*"
                                @change="handleImageUpload"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                    >Name</label
                                >
                                <input
                                    v-model="form.name"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"
                                    :class="{
                                        'border-rose-500': form.errors.name,
                                    }"
                                />
                                <div
                                    v-if="form.errors.name"
                                    class="text-[10px] text-rose-500 ml-1"
                                >
                                    {{ form.errors.name }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                    >Code</label
                                >
                                <input
                                    v-model="form.product_code"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"
                                    :class="{
                                        'border-rose-500':
                                            form.errors.product_code,
                                    }"
                                />
                                <div
                                    v-if="form.errors.product_code"
                                    class="text-[10px] text-rose-500 ml-1"
                                >
                                    {{ form.errors.product_code }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                    >Brand</label
                                >
                                <select
                                    v-model="form.brand_id"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none"
                                    :class="{
                                        'border-rose-500': form.errors.brand_id,
                                    }"
                                >
                                    <option value="">Select Brand</option>
                                    <option
                                        v-for="b in brands"
                                        :key="b.id"
                                        :value="b.id"
                                    >
                                        {{ b.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.brand_id"
                                    class="text-[10px] text-rose-500 ml-1"
                                >
                                    {{ form.errors.brand_id }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                    >Category</label
                                >
                                <select
                                    v-model="form.category_id"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none"
                                    :class="{
                                        'border-rose-500':
                                            form.errors.category_id,
                                    }"
                                >
                                    <option value="">Select Category</option>
                                    <option
                                        v-for="c in filteredCategories"
                                        :key="c.id"
                                        :value="c.id"
                                    >
                                        {{ c.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.category_id"
                                    class="text-[10px] text-rose-500 ml-1"
                                >
                                    {{ form.errors.category_id }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                    >Price</label
                                >
                                <input
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"
                                    :class="{
                                        'border-rose-500': form.errors.price,
                                    }"
                                />
                                <div
                                    v-if="form.errors.price"
                                    class="text-[10px] text-rose-500 ml-1"
                                >
                                    {{ form.errors.price }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                    >Stock</label
                                >
                                <input
                                    v-model="form.stock"
                                    type="number"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none"
                                    :class="{
                                        'border-rose-500': form.errors.stock,
                                    }"
                                />
                                <div
                                    v-if="form.errors.stock"
                                    class="text-[10px] text-rose-500 ml-1"
                                >
                                    {{ form.errors.stock }}
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                >Description</label
                            >
                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none resize-none"
                            ></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                    >Status</label
                                >
                                <select
                                    v-model="form.status"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none"
                                >
                                    <option :value="true">Active</option>
                                    <option :value="false">Inactive</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-bold text-slate-400 uppercase ml-1"
                                    >Stock Status</label
                                >
                                <select
                                    v-model="form.status_stock"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none"
                                >
                                    <option
                                        v-for="ss in statusStocks"
                                        :key="ss.id"
                                        :value="ss.id"
                                    >
                                        {{ ss.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div
                        class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0"
                    >
                        <button
                            @click="showModal = false"
                            class="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="save"
                            :disabled="form.processing"
                            class="px-8 py-2.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white font-bold rounded-xl shadow-lg shadow-green-500/20 active:scale-95 transition-all"
                        >
                            {{ form.processing ? "Saving..." : "Save changes" }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
/* Same animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.animate-pop {
    animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes pop {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
