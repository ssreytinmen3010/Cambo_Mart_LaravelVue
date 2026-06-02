<script setup>
import { onMounted, onBeforeUnmount, ref, watch, nextTick } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({
            orders: 0,
            users: 0,
            products: 0,
            categories: 0,
            brands: 0,
            total_stock: 0,
            paid_amount: 0,
            unpaid_amount: 0,
        }),
    },
    monthlyChart: {
        type: Array,
        default: () => [],
    },
    bestSellers: {
        type: Array,
        default: () => [],
    },
    bestSellerMonthly: {
        type: Object,
        default: () => ({ months: [], series: [] }),
    },
});

const paymentStatus = ref(props.filters?.payment_status || "");
const orderStatus = ref(props.filters?.order_status || "");
const fromDate = ref(props.filters?.from_date || "");
const toDate = ref(props.filters?.to_date || "");

watch([paymentStatus, orderStatus, fromDate, toDate], ([payment, order, from, to]) => {
    const params = {};
    if (payment) params.payment_status = payment;
    if (order) params.order_status = order;
    if (from) params.from_date = from;
    if (to) params.to_date = to;

    router.get(route("admin.dashboard"), params, {
        preserveState: true,
        replace: true,
    });
});

const revenueChartRef = ref(null);
const orderCountChartRef = ref(null);
const bestSellerChartRef = ref(null);
const bestSellerMonthlyChartRef = ref(null);

let revenueChart = null;
let orderCountChart = null;
let bestSellerChart = null;
let bestSellerMonthlyChart = null;
let Chart = null;

async function loadChart() {
    if (!Chart) {
        const module = await import("chart.js/auto");
        Chart = module.default;
    }

    return Chart;
}

function formatMoney(value) {    return `$${Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function destroyCharts() {
    revenueChart?.destroy();
    orderCountChart?.destroy();
    bestSellerChart?.destroy();
    bestSellerMonthlyChart?.destroy();
    revenueChart = null;
    orderCountChart = null;
    bestSellerChart = null;
    bestSellerMonthlyChart = null;
}

async function renderCharts() {
    destroyCharts();

    const ChartConstructor = await loadChart();
    if (!ChartConstructor) return;

    const labels = props.monthlyChart.map((row) => row.label);

    if (revenueChartRef.value) {
        revenueChart = new ChartConstructor(revenueChartRef.value, {            type: "bar",
            data: {
                labels,
                datasets: [
                    {
                        label: "Paid",
                        data: props.monthlyChart.map((row) => row.paid_amount),
                        backgroundColor: "rgba(16, 185, 129, 0.85)",
                        borderRadius: 8,
                    },
                    {
                        label: "Unpaid",
                        data: props.monthlyChart.map((row) => row.unpaid_amount),
                        backgroundColor: "rgba(245, 158, 11, 0.85)",
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "top" },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `$${value}`,
                        },
                    },
                },
            },
        });
    }

    if (orderCountChartRef.value) {
        orderCountChart = new ChartConstructor(orderCountChartRef.value, {            type: "bar",
            data: {
                labels,
                datasets: [
                    {
                        label: "Orders",
                        data: props.monthlyChart.map((row) => row.order_count),
                        backgroundColor: "rgba(59, 130, 246, 0.85)",
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                    },
                },
            },
        });
    }

    if (bestSellerChartRef.value && props.bestSellers.length) {
        bestSellerChart = new ChartConstructor(bestSellerChartRef.value, {
            type: "bar",
            data: {
                labels: props.bestSellers.map((item) => item.name),
                datasets: [
                    {
                        label: "Units sold",
                        data: props.bestSellers.map((item) => item.total_qty),
                        backgroundColor: "rgba(139, 92, 246, 0.85)",
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                indexAxis: "y",
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                    },
                },
            },
        });
    }

    if (
        bestSellerMonthlyChartRef.value &&
        props.bestSellerMonthly.series?.length
    ) {
        const palette = [
            "rgba(16, 185, 129, 1)",
            "rgba(59, 130, 246, 1)",
            "rgba(245, 158, 11, 1)",
            "rgba(239, 68, 68, 1)",
            "rgba(139, 92, 246, 1)",
        ];

        bestSellerMonthlyChart = new ChartConstructor(bestSellerMonthlyChartRef.value, {            type: "line",
            data: {
                labels: props.bestSellerMonthly.months,
                datasets: props.bestSellerMonthly.series.map((serie, index) => ({
                    label: serie.name,
                    data: serie.data,
                    borderColor: palette[index % palette.length],
                    backgroundColor: palette[index % palette.length].replace(
                        "1)",
                        "0.15)",
                    ),
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "top" },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                    },
                },
            },
        });
    }
}

onMounted(async () => {
    await nextTick();
    await renderCharts();
});

watch(
    () => [props.monthlyChart, props.bestSellers, props.bestSellerMonthly],
    async () => {
        await nextTick();
        await renderCharts();
    },
    { deep: true },
);
onBeforeUnmount(() => {
    destroyCharts();
});
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout title="Dashboard" subtitle="Overview of sales and inventory">
        <div
            class="mb-6 bg-white p-1.5 rounded-[2rem] border border-slate-200/60 shadow-sm"
        >
            <div class="flex flex-col lg:flex-row items-center gap-2">
                <div
                    class="flex items-center gap-2 p-1 bg-slate-50/50 rounded-[1.5rem] w-full lg:w-auto"
                >
                    <div class="flex items-center gap-2 px-3">
                        <v-icon size="18" class="text-slate-400"
                            >mdi-calendar-range</v-icon
                        >
                        <span
                            class="text-[10px] font-black text-slate-400 uppercase tracking-tighter hidden sm:inline"
                            >Date Range</span
                        >
                    </div>
                    <div
                        class="flex items-center gap-1.5 bg-white p-1 rounded-2xl shadow-sm border border-slate-100"
                    >
                        <input
                            v-model="fromDate"
                            type="date"
                            class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer"
                        />
                        <div class="w-[1px] h-4 bg-slate-200"></div>
                        <input
                            v-model="toDate"
                            type="date"
                            class="px-3 py-1.5 bg-transparent border-none text-[12px] font-bold text-slate-600 focus:ring-0 outline-none cursor-pointer"
                        />
                    </div>
                </div>

                <select
                    v-model="paymentStatus"
                    class="w-full lg:w-auto px-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-xs font-bold text-slate-500 outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                    <option value="">All Payment Status</option>
                    <option value="PENDING">Pending</option>
                    <option value="PAID">Paid</option>
                    <option value="FAILED">Failed</option>
                    <option value="REFUNDED">Refunded</option>
                </select>

                <select
                    v-model="orderStatus"
                    class="w-full lg:w-auto px-4 py-3 bg-slate-50/50 border-none rounded-[1.5rem] text-xs font-bold text-slate-500 outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                    <option value="">All Order Status</option>
                    <option value="PENDING">Pending</option>
                    <option value="COMPLETED">Completed</option>
                    <option value="CANCELLED">Cancelled</option>
                    <option value="REFUNDED">Refunded</option>
                </select>

                <button
                    v-if="paymentStatus || orderStatus || fromDate || toDate"
                    @click="
                        paymentStatus = '';
                        orderStatus = '';
                        fromDate = '';
                        toDate = '';
                    "
                    class="w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 active:scale-90 transition-all shrink-0"
                    title="Reset filters"
                >
                    <v-icon size="16">mdi-filter-off</v-icon>
                </button>
            </div>
        </div>

        <div
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8"
        >
            <div
                class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm 2xl:col-span-1"
            >
                <span
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
                    >Total Orders</span
                >
                <h3 class="mt-2 text-2xl font-black text-slate-800">
                    {{ stats.orders }}
                </h3>
            </div>

            <div
                class="bg-white p-5 rounded-3xl border border-emerald-100 shadow-sm 2xl:col-span-1"
            >
                <span
                    class="text-[10px] font-black text-emerald-600 uppercase tracking-widest"
                    >Paid Amount</span
                >
                <h3 class="mt-2 text-2xl font-black text-emerald-700">
                    {{ formatMoney(stats.paid_amount) }}
                </h3>
            </div>

            <div
                class="bg-white p-5 rounded-3xl border border-amber-100 shadow-sm 2xl:col-span-1"
            >
                <span
                    class="text-[10px] font-black text-amber-600 uppercase tracking-widest"
                    >Unpaid Amount</span
                >
                <h3 class="mt-2 text-2xl font-black text-amber-700">
                    {{ formatMoney(stats.unpaid_amount) }}
                </h3>
            </div>

            <div
                class="bg-white p-5 rounded-3xl border border-blue-100 shadow-sm 2xl:col-span-1"
            >
                <span
                    class="text-[10px] font-black text-blue-600 uppercase tracking-widest"
                    >Total Stock</span
                >
                <h3 class="mt-2 text-2xl font-black text-blue-700">
                    {{ stats.total_stock }}
                </h3>
                <p class="mt-1 text-[10px] text-slate-400">All product items</p>
            </div>

            <div
                class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm 2xl:col-span-1"
            >
                <span
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
                    >Users</span
                >
                <h3 class="mt-2 text-2xl font-black text-slate-800">
                    {{ stats.users }}
                </h3>
            </div>

            <div
                class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm 2xl:col-span-1"
            >
                <span
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
                    >Products</span
                >
                <h3 class="mt-2 text-2xl font-black text-slate-800">
                    {{ stats.products }}
                </h3>
            </div>

            <div
                class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm 2xl:col-span-1"
            >
                <span
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
                    >Categories</span
                >
                <h3 class="mt-2 text-2xl font-black text-slate-800">
                    {{ stats.categories }}
                </h3>
            </div>

            <div
                class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm 2xl:col-span-1"
            >
                <span
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
                    >Brands</span
                >
                <h3 class="mt-2 text-2xl font-black text-slate-800">
                    {{ stats.brands }}
                </h3>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black text-slate-800">
                            Revenue histogram
                        </h2>
                        <p class="text-[11px] text-slate-400">
                            Paid vs unpaid order amounts
                            <span v-if="fromDate || toDate">in selected period</span>
                            <span v-else>(all time)</span>
                        </p>
                    </div>
                    <Link
                        :href="route('admin.orders.index')"
                        class="text-[11px] font-bold text-emerald-600"
                    >
                        View orders
                    </Link>
                </div>
                <div class="h-72">
                    <canvas ref="revenueChartRef"></canvas>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm"
            >
                <div class="mb-4">
                    <h2 class="text-sm font-black text-slate-800">
                        Orders histogram
                    </h2>
                    <p class="text-[11px] text-slate-400">
                        Total orders per month
                        <span v-if="fromDate || toDate">in selected period</span>
                        <span v-else>(all time)</span>
                    </p>
                </div>
                <div class="h-72">
                    <canvas ref="orderCountChartRef"></canvas>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm"
            >
                <div class="mb-4">
                    <h2 class="text-sm font-black text-slate-800">
                        Best seller products
                    </h2>
                    <p class="text-[11px] text-slate-400">
                        Top products by quantity sold
                    </p>
                </div>
                <div v-if="bestSellers.length" class="h-80">
                    <canvas ref="bestSellerChartRef"></canvas>
                </div>
                <p v-else class="text-sm text-slate-400 py-16 text-center">
                    No sales data yet.
                </p>
            </div>

            <div
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm"
            >
                <div class="mb-4">
                    <h2 class="text-sm font-black text-slate-800">
                        Best sellers by month
                    </h2>
                    <p class="text-[11px] text-slate-400">
                        Monthly units sold for top products
                    </p>
                </div>
                <div
                    v-if="bestSellerMonthly.series?.length"
                    class="h-80"
                >
                    <canvas ref="bestSellerMonthlyChartRef"></canvas>
                </div>
                <p v-else class="text-sm text-slate-400 py-16 text-center">
                    No monthly sales data yet.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>
