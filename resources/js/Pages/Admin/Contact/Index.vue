<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    contacts: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const selectedContact = ref(props.contacts?.data?.[0] ?? null);

const contacts = computed(() => props.contacts?.data ?? []);

watch(search, (value) => {
    router.get(route('admin.contacts.index'), { search: value }, { preserveState: true, replace: true });
});

watch(
    contacts,
    (value) => {
        if (!value.length) {
            selectedContact.value = null;
            return;
        }

        const stillVisible = value.some((contact) => contact.id === selectedContact.value?.id);
        if (!stillVisible) {
            selectedContact.value = value[0];
        }
    },
    { immediate: true },
);

const openContact = (contact) => {
    selectedContact.value = contact;
};

const clearSearch = () => {
    search.value = '';
};

const messagePreview = (message) => {
    const text = String(message ?? '').trim();
    return text.length > 110 ? `${text.slice(0, 110)}...` : text;
};
</script>

<template>
    <AdminLayout title="Contacts" subtitle="Review messages sent from the public contact form">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-lg font-black text-slate-800 tracking-tight">Contact messages</h1>
                <p class="mt-1 text-sm text-slate-500">Query records from the `contacts` table and inspect each message.</p>
            </div>

            <div class="w-full lg:max-w-md">
                <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2">Search</label>
                <div class="relative">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search name, email, subject, or message..."
                        class="w-full rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 pr-20 text-sm shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-200"
                        @click="clearSearch"
                    >
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3 mb-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Total messages</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ props.contacts?.total ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Current page</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ contacts.length }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Selected</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ selectedContact ? '1' : '0' }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_360px]">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-[11px] font-black uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Contact</th>
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-6 py-4 w-1/3">Message</th>
                                <th class="px-6 py-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="contact in contacts"
                                :key="contact.id"
                                class="cursor-pointer transition hover:bg-emerald-50/50"
                                :class="selectedContact?.id === contact.id ? 'bg-emerald-50/80' : ''"
                                @click="openContact(contact)"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ contact.full_name }}</div>
                                    <div class="text-xs text-slate-400">#{{ contact.id }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div>{{ contact.email }}</div>
                                    <div v-if="contact.user_name" class="text-xs text-slate-400">User: {{ contact.user_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ contact.subject }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ messagePreview(contact.message) }}
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-400">
                                    {{ contact.created_at }}
                                </td>
                            </tr>

                            <tr v-if="!contacts.length">
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <p class="text-sm font-semibold text-slate-700">No contact messages found.</p>
                                    <p class="mt-1 text-sm text-slate-500">Try adjusting the search query.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="props.contacts?.links?.length" class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-6 py-4">
                    <p class="text-xs font-medium text-slate-500">
                        Showing {{ props.contacts.from ?? 0 }} to {{ props.contacts.to ?? 0 }} of {{ props.contacts.total ?? 0 }} messages
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="(link, index) in props.contacts.links"
                            :key="index"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="rounded-xl px-3 py-1.5 text-xs font-semibold transition"
                            :class="link.active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        />
                    </div>
                </div>
            </div>

            <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-6">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Message details</p>

                <template v-if="selectedContact">
                    <h2 class="mt-3 text-2xl font-black text-slate-900">{{ selectedContact.full_name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ selectedContact.email }}</p>

                    <div class="mt-5 space-y-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Subject</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ selectedContact.subject }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Message</p>
                            <p class="mt-1 whitespace-pre-line rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                                {{ selectedContact.message }}
                            </p>
                        </div>

                        <div class="grid gap-3 text-sm">
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">User ID</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ selectedContact.user_id ?? 'Guest' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Submitted</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ selectedContact.created_at }}</p>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                        Select a message to preview its full content.
                    </div>
                </template>
            </aside>
        </div>
    </AdminLayout>
</template>
