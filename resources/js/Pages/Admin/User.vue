<script setup>
import { ref, computed } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router, Link } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  users: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  roles: {
    type: Array,
    default: () => []
  }
});

// State
const searchName = ref("");
const filterRole = ref("");

// Modal state
const showModal = ref(false);
const isEdit = ref(false);
const currentUser = ref({
  id: null,
  name: "",
  email: "",
  phone: "",
  password: "",
  role_id: "",
  status: "Active"
});

const filteredUsers = computed(() => {
  if (!props.users?.data) return [];
  return props.users.data.filter(user => {
    const matchesName = user.name.toLowerCase().includes(searchName.value.toLowerCase());
    const matchesRole = filterRole.value ? user.role?.name === filterRole.value : true;
    return matchesName && matchesRole;
  });
});

// Actions
function resetPassword(userId) {
  alert("Reset password for user ID: " + userId);
}

async function toggleStatus(user) {
  try {
    const newStatus = user.status === "Active" || user.status === "active" ? "Inactive" : "Active";
    await axios.patch(`/admin/users/${user.id}/status`, {
      status: newStatus
    });
    router.reload();
  } catch (e) {
    console.error(e);
    alert("Failed to update status");
  }
}

function addUser() {
  isEdit.value = false;
  currentUser.value = { id: null, name: "", email: "", phone: "", password: "", role_id: "", status: "Active" };
  showModal.value = true;
}

function editUser(user) {
  isEdit.value = true;
  currentUser.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: user.phone,
    role_id: user.role?.id || "",
    status: user.status || "Active"
  };
  showModal.value = true;
}

async function saveUser() {
  try {
    if (isEdit.value) {
      await axios.patch(`/admin/users/list/update/${currentUser.value.id}`, currentUser.value);
      alert("User updated successfully");
    } else {
      // For new users, don't send the id field
      const { id, ...userData } = currentUser.value;
      await axios.post("/admin/users/list/add", userData);
      alert("User added successfully");
    }
    showModal.value = false;
    router.reload();
  } catch (e) {
    console.error("Save error:", e);
    if (e.response && e.response.data && e.response.data.errors) {
      const errors = Object.values(e.response.data.errors).flat().join('\n');
      alert("Failed to save user:\n" + errors);
    } else {
      alert("Failed to save user");
    }
  }
}
</script>

<template>
  <AdminLayout title="Users" subtitle="Manage your platform users">
    
<div class="flex items-center justify-between mb-4">
  <h1 class="text-lg font-black text-slate-800 tracking-tight">Users List</h1>

  <button
    @click="addUser"
    class="px-3 py-1.5 bg-gradient-to-tr from-green-600 to-emerald-400 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-green-500/20 transition-all flex items-center gap-1.5 active:scale-95"
  >
    <v-icon color="white" size="14">mdi-plus</v-icon>
    New User
  </button>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-4 bg-white p-2 rounded-3xl  border-slate-100 shadow-sm">
  <div class="sm:col-span-2 relative">
    <!-- <v-icon 
      class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" 
      size="16"
    >
      mdi-magnify
    </v-icon> -->
    <input
      v-model="searchName"
      type="text"
      placeholder="Search..."
      class="w-50 pl-9 pr-4 py-1.5 bg-slate-50 border border-slate-100 rounded-full text-xs focus:ring-4 focus:ring-green-500/10 focus:border-green-400 focus:bg-white outline-none transition-all placeholder:text-slate-400"
    />
  </div>

  <div class="relative">
    <!-- <v-icon 
      class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" 
      size="16"
    >
      mdi-filter-variant
    </v-icon> -->
    <select
      v-model="filterRole"
      class="w-full pl-9 pr-8 py-1.5 bg-slate-50 border border-slate-100 rounded-full text-xs font-medium text-slate-600 focus:ring-4 focus:ring-green-500/10 focus:border-green-400 focus:bg-white outline-none appearance-none cursor-pointer transition-all"
    >
      <option value="">All Roles</option>
      <option v-for="role in props.roles" :key="role.id" :value="role.name">{{ role.name }}</option>
    </select>
    <!-- <v-icon 
      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" 
      size="14"
    >
      mdi-chevron-down
    </v-icon> -->
  </div>
</div>

   <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
  <table class="min-w-full divide-y divide-slate-200">
    <thead class="bg-slate-50">
      <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
        <th class="px-6 py-3">User Details</th>
        <th class="px-6 py-3">Phone</th>
        <th class="px-6 py-3">Role</th>
        <th class="px-6 py-3">Joined</th>
        <th class="px-6 py-3 text-center">Status</th>
        <th class="px-6 py-3 text-right">Actions</th>
      </tr>
    </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-if="filteredUsers.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <v-icon size="48" class="text-slate-300">mdi-account-group-outline</v-icon>
                <p class="text-sm font-medium">No users found</p>
              </div>
            </td>
          </tr>
          <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-green-100 text-green-600 flex items-center justify-center font-bold text-sm">
                   {{ user.name.charAt(0) }}
                </div>
                <div>
                  <div class="text-sm font-bold text-slate-700">{{ user.name }}</div>
                  <div class="text-xs text-slate-400">{{ user.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-slate-600">{{ user.phone }}</td>
            <td class="px-6 py-4 text-sm text-slate-600 font-medium">{{ user.role?.name }}</td>
            <td class="px-6 py-4 text-sm text-slate-400">{{ new Date(user.created_at).toLocaleDateString() }}</td>
            <td class="px-6 py-4">
              <span :class="(user.status === 'active' || user.status === 'Active') ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                {{ user.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
               <div class="flex justify-end gap-2">
                  <button @click="editUser(user)" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                    <v-icon size="18">mdi-pencil</v-icon>
                  </button>
                  <button @click="toggleStatus(user)" :class="(user.status === 'active' || user.status === 'Active') ? 'text-rose-500 hover:bg-rose-50' : 'text-emerald-500 hover:bg-emerald-50'" class="p-2 rounded-lg transition-colors">
                    <v-icon size="18">{{ (user.status === 'active' || user.status === 'Active') ? 'mdi-account-off' : 'mdi-account-check' }}</v-icon>
                  </button>
               </div>
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination Controls -->
      <div v-if="users?.links" class="p-3 border-t border-slate-100 flex justify-center gap-1">
         <Link v-for="(link, i) in users.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded" :class="link.active ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-gray-100'" />
      </div>
    </div>

    <Transition name="modal-fade">
      <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="showModal = false"></div>

        <div class="relative bg-white w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-pop">
          <div class="bg-gradient-to-tr from-green-600 to-emerald-400 px-8 py-6 text-white">
            <h2 class="text-xl font-black tracking-tight">{{ isEdit ? 'Edit User' : 'Add New User' }}</h2>
          </div>

          <div class="p-8 space-y-4">
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Full Name</label>
              <input v-model="currentUser.name" placeholder="Name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all"/>
            </div>
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Email</label>
              <input v-model="currentUser.email" placeholder="Email" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all"/>
            </div>
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Phone</label>
              <input v-model="currentUser.phone" placeholder="Phone" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all"/>
            </div>
            <div class="space-y-1" v-if="!isEdit">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Password</label>
              <input v-model="currentUser.password" type="password" placeholder="Password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all"/>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Role</label>
                <select v-model="currentUser.role_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none cursor-pointer">
                  <option value="">Select Role</option>
                  <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
              </div>
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Status</label>
                <select v-model="currentUser.status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none appearance-none cursor-pointer">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button @click="showModal=false" class="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">Cancel</button>
            <button @click="saveUser" class="px-8 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 shadow-lg shadow-slate-200 active:scale-95 transition-all">Save Changes</button>
          </div>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.animate-pop {
  animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes pop {
  from { opacity: 0; transform: translateY(20px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>