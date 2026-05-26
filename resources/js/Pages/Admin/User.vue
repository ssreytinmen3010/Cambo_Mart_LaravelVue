<script setup>
import { ref, computed } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router, Link } from "@inertiajs/vue3";
import axiosLib from "axios";

const axios = typeof window !== "undefined" && window.axios ? window.axios : axiosLib;

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
const showViewModal = ref(false);
const showPasswordModal = ref(false);
const openMenuId = ref(null);
const isEdit = ref(false);
const viewUser = ref(null);

const passwordForm = ref({
  password: "",
  password_confirmation: ""
});

function toggleMenu(id) {
  openMenuId.value = openMenuId.value === id ? null : id;
}

// Close dropdown on click outside
if (typeof window !== "undefined") {
  window.addEventListener("click", (e) => {
    if (!e.target.closest(".action-dropdown")) {
      openMenuId.value = null;
    }
  });
}
const currentUser = ref({
  id: null,
  name: "",
  email: "",
  phone: "",
  password: "",
  role_id: "",
  status: "Active",
  image: null
});

const filteredUsers = computed(() => {
  if (!props.users?.data || !Array.isArray(props.users.data)) return [];
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
  currentUser.value = { id: null, name: "", email: "", phone: "", password: "", role_id: "", status: "Active", image: null };
  showModal.value = true;
}

function openViewModal(user) {
  viewUser.value = user;
  showViewModal.value = true;
}

function editUser(user) {
  isEdit.value = true;
  currentUser.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: user.phone,
    role_id: user.role?.id || "",
    status: user.status || "Active",
    image: user.image || null
  };
  showModal.value = true;
}

function openPasswordModal(user) {
  currentUser.value = { id: user.id, name: user.name };
  passwordForm.value = { password: "", password_confirmation: "" };
  showPasswordModal.value = true;
}

async function changePassword() {
  if (!passwordForm.value.password || passwordForm.value.password.length < 6) {
    alert("Password must be at least 6 characters");
    return;
  }
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    alert("Passwords do not match");
    return;
  }

  try {
    await axios.patch(`/admin/users/${currentUser.value.id}/password`, passwordForm.value);
    alert("Password updated successfully");
    showPasswordModal.value = false;
  } catch (e) {
    console.error("Password change error:", e);
    const msg = e.response?.data?.message || "Failed to update password";
    alert(msg);
  }
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

async function onFileSelected(event) {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("image", file);

  try {
    const response = await axios.post("/upload-image", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    currentUser.value.image = response.data.url;
  } catch (error) {
    console.error("Image upload failed:", error);
    alert("Image upload failed");
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

   <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
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
                <div v-if="user.image" class="w-9 h-9 rounded-xl overflow-hidden border border-slate-100 shadow-sm">
                  <img :src="user.image" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-9 h-9 rounded-xl bg-green-100 text-green-600 flex items-center justify-center font-bold text-sm">
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
            <td class="px-6 py-4 ml-2 text-center">
              <span :class="(user.status === 'active' || user.status === 'Active') ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                {{ user.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <!-- Dropdown Action -->
              <div class="relative flex justify-end action-dropdown">
                <button 
                  @click.stop="toggleMenu(user.id)"
                  class="p-2 hover:bg-slate-100 rounded-full transition-all text-slate-400 hover:text-green-600 active:scale-90"
                >
                  <v-icon size="20">mdi-dots-vertical</v-icon>
                </button>

                <!-- Dropdown Menu -->
                <transition name="scale-fade">
                  <div 
                    v-if="openMenuId === user.id"
                    class="absolute right-0 top-11 w-44 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50 overflow-hidden ring-4 ring-slate-900/5"
                  >
                    <button 
                      @click="openViewModal(user); openMenuId = null"
                      class="w-full px-4 py-2.5 text-left text-xs font-bold text-slate-600 hover:bg-green-50 hover:text-green-600 flex items-center gap-3 transition-colors"
                    >
                      <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                        <v-icon size="14">mdi-eye-outline</v-icon>
                      </div>
                      View Detail
                    </button>

                    <button 
                      @click="editUser(user); openMenuId = null"
                      class="w-full px-4 py-2.5 text-left text-xs font-bold text-slate-600 hover:bg-green-50 hover:text-green-600 flex items-center gap-3 transition-colors underline-offset-4"
                    >
                      <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                        <v-icon size="14">mdi-pencil</v-icon>
                      </div>
                      Edit User
                    </button>

                    <button 
                      @click="openPasswordModal(user); openMenuId = null"
                      class="w-full px-4 py-2.5 text-left text-xs font-bold text-slate-600 hover:bg-green-50 hover:text-green-600 flex items-center gap-3 transition-colors underline-offset-4"
                    >
                      <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                        <v-icon size="14">mdi-lock-reset</v-icon>
                      </div>
                      Change Password
                    </button>

                    <div class="my-1 border-t border-slate-50"></div>

                    <button 
                      @click="toggleStatus(user); openMenuId = null"
                      :class="(user.status === 'active' || user.status === 'Active') ? 'hover:bg-rose-50 hover:text-rose-600' : 'hover:bg-emerald-50 hover:text-emerald-600'"
                      class="w-full px-4 py-2.5 text-left text-xs font-bold text-slate-600 flex items-center gap-3 transition-colors"
                    >
                      <div :class="(user.status === 'active' || user.status === 'Active') ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'" class="w-7 h-7 rounded-lg flex items-center justify-center">
                        <v-icon size="14">{{ (user.status === 'active' || user.status === 'Active') ? 'mdi-account-off' : 'mdi-account-check' }}</v-icon>
                      </div>
                      {{ (user.status === 'active' || user.status === 'Active') ? 'Deactivate' : 'Activate' }}
                    </button>
                  </div>
                </transition>
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

        <div class="relative bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden animate-pop flex flex-col border border-slate-100">
          <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="text-left">
              <h2 class="text-xl font-black tracking-tight uppercase text-slate-800">{{ isEdit ? 'Edit User' : 'Add New User' }}</h2>
            </div>
            <button @click="showModal = false" class="absolute right-6 p-2 hover:bg-slate-100 rounded-xl transition-colors text-slate-400 hover:text-slate-600">
              <v-icon icon="mdi-close" size="24" />
            </button>
          </div>

          <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-5 overflow-y-auto">
            <div class="col-span-full flex flex-col items-center mb-4">
              <div class="relative group">
                <div class="w-24 h-24 rounded-3xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all group-hover:border-green-400">
                  <img v-if="currentUser.image" :src="currentUser.image" class="w-full h-full object-cover" />
                  <v-icon v-else icon="mdi-camera-outline" size="32" class="text-slate-300" />
                  
                  <input 
                    type="file" 
                    @change="onFileSelected" 
                    accept="image/*"
                    class="absolute inset-0 opacity-0 cursor-pointer"
                  />
                </div>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-white rounded-xl shadow-lg border border-slate-100 flex items-center justify-center text-green-600">
                  <v-icon icon="mdi-plus" size="18" />
                </div>
              </div>
              <p class="text-[10px] font-bold text-slate-400 uppercase mt-3 tracking-widest">Profile Photo</p>
            </div>

            <div class="space-y-4">
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Full Name</label>
                <input v-model="currentUser.name" placeholder="Name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all text-sm"/>
              </div>
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Email Address</label>
                <input v-model="currentUser.email" placeholder="Email" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all text-sm"/>
              </div>
            </div>

            <div class="space-y-4">
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Phone Number</label>
                <input v-model="currentUser.phone" placeholder="Phone" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all text-sm"/>
              </div>
              <div class="space-y-1" v-if="!isEdit">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Secure Password</label>
                <input v-model="currentUser.password" type="password" placeholder="Password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-green-500/10 focus:border-green-400 outline-none transition-all text-sm"/>
              </div>
              <div class="space-y-1" v-else>
                 <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Account Password</label>
                 <div class="w-full px-4 py-3 bg-slate-100/50 border border-slate-200 rounded-xl text-[10px] font-bold text-slate-400 italic font-medium">Password hidden for security reasons</div>
              </div>
            </div>
            
            <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">User Role</label>
                <select v-model="currentUser.role_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none appearance-none cursor-pointer text-sm">
                  <option value="">Select Role</option>
                  <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
              </div>
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Account Status</label>
                <select v-model="currentUser.status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none appearance-none cursor-pointer text-sm">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>

          <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
            <button @click="showModal=false" class="px-6 py-2 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Discard</button>
            <button @click="saveUser" class="px-10 py-2.5 bg-emerald-600 text-white font-black text-xs uppercase tracking-widest rounded-xl hover:bg-emerald-700 shadow-xl shadow-emerald-200 active:scale-95 transition-all">Save Changes</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- CHANGE PASSWORD MODAL -->
    <Transition name="modal-fade">
      <div v-if="showPasswordModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
        <div class="relative bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden animate-pop border border-slate-200 flex flex-col">
          <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="text-left">
              <h2 class="text-xl font-black tracking-tight uppercase text-slate-800">Change Password</h2>
            </div>
            <button @click="showPasswordModal = false" class="absolute right-6 p-2 hover:bg-slate-100 rounded-xl transition-colors text-slate-400 hover:text-slate-600">
              <v-icon icon="mdi-close" size="24" />
            </button>
          </div>

          <div class="p-8 space-y-5 flex-1 flex flex-col justify-center w-full">
            <div class="space-y-2">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 tracking-widest">New Password</label>
              <div class="relative">
                <v-icon icon="mdi-lock-outline" size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500" />
                <input 
                  v-model="passwordForm.password" 
                  type="password"
                  placeholder="At least 6 characters" 
                  class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-400 outline-none transition-all text-sm"
                />
              </div>
            </div>
            
            <div class="space-y-2">
              <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 tracking-widest">Confirm Password</label>
              <div class="relative">
                <v-icon icon="mdi-lock-check-outline" size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500" />
                <input 
                  v-model="passwordForm.password_confirmation" 
                  type="password"
                  placeholder="Repeat new password" 
                  class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-400 outline-none transition-all text-sm"
                />
              </div>
            </div>
          </div>

          <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
            <button @click="showPasswordModal = false" class="px-6 py-2 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Cancel</button>
            <button @click="changePassword" class="px-8 py-2.5 bg-emerald-600 text-white font-black text-xs uppercase tracking-widest rounded-xl hover:bg-emerald-700 shadow-xl shadow-emerald-200 active:scale-95 transition-all">Update Key</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- VIEW USER MODAL -->
    <Transition name="modal-fade">
      <div v-if="showViewModal && viewUser" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
        <div class="bg-white w-full max-w-5xl rounded-2xl shadow-xl overflow-hidden flex flex-col border border-slate-200">
          <!-- Header -->
          <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <v-icon icon="mdi-account-details-outline" size="28" />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-900">User Profile Details</h3>
                <p class="text-sm text-slate-500">Overview of account information and status</p>
              </div>
            </div>
            <button @click="showViewModal = false" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-slate-600">
              <v-icon icon="mdi-close" size="24" />
            </button>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto p-10 bg-white">
            <div class="grid grid-cols-12 gap-10">
              <!-- Left: Identity -->
              <div class="col-span-4 space-y-6">
                <div class="p-8 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col items-center text-center">
                  <div v-if="viewUser?.image" class="w-28 h-28 rounded-3xl overflow-hidden border-4 border-white shadow-xl mb-4 group transition-all hover:scale-105">
                    <img :src="viewUser.image" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-28 h-28 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-4xl font-bold mb-4 border-4 border-white shadow-sm">
                    {{ viewUser?.name?.charAt(0).toUpperCase() }}
                  </div>
                  <h4 class="text-xl font-bold text-slate-900">{{ viewUser?.name }}</h4>
                  <div class="mt-3 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider" 
                    :class="viewUser?.role?.name === 'Admin' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'">
                    {{ viewUser?.role?.name }}
                  </div>
                </div>

                  <div class="space-y-4">
                  <div class="p-5 rounded-xl border border-slate-100 bg-white shadow-sm group">
                    <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 block mb-2">Account Status</span>
                    <div class="flex items-center gap-2">
                       <div class="w-2.5 h-2.5 rounded-full relative" :class="viewUser?.status === 'Active' || viewUser?.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500'">
                         <div class="absolute inset-0 rounded-full animate-ping opacity-75" :class="viewUser?.status === 'Active' || viewUser?.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                       </div>
                       <span class="text-sm font-bold transition-all duration-300" :class="viewUser?.status === 'Active' || viewUser?.status === 'active' ? 'text-emerald-500' : 'text-rose-500'">{{ viewUser?.status }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right: Detailed Info -->
              <div class="col-span-8 space-y-10">
                <div>
                  <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                    <v-icon icon="mdi-account-outline" size="18" class="text-emerald-500" />
                    General Information
                  </h5>
                  <div class="grid grid-cols-2 gap-x-12 gap-y-8">
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email Address</label>
                      <p class="text-slate-900 font-semibold flex items-center gap-2">
                        <v-icon icon="mdi-email-outline" size="16" class="text-slate-300" />
                        {{ viewUser?.email }}
                      </p>
                    </div>
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Phone Number</label>
                      <p class="text-slate-900 font-semibold flex items-center gap-2">
                        <v-icon icon="mdi-phone-outline" size="16" class="text-slate-300" />
                        {{ viewUser?.phone || 'Not available' }}
                      </p>
                    </div>
                  </div>
                </div>

                <div class="pt-10 border-t border-slate-100">
                  <h5 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                    <v-icon icon="mdi-history" size="18" class="text-emerald-500" />
                    Administrative History
                  </h5>
                  <div class="grid grid-cols-2 gap-x-12 gap-y-8">
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Member Since</label>
                      <p class="text-slate-900 font-semibold text-sm">{{ viewUser?.created_at ? new Date(viewUser.created_at).toLocaleDateString(undefined, { dateStyle: 'long' }) : 'N/A' }}</p>
                    </div>
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Last Sync</label>
                      <p class="text-slate-900 font-semibold text-sm">{{ viewUser?.updated_at ? new Date(viewUser.updated_at).toLocaleDateString(undefined, { dateStyle: 'long' }) : 'N/A' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button @click="showViewModal = false" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-white transition-all text-sm uppercase tracking-wider">
              Close
            </button>
            <button 
              @click="editUser(viewUser); showViewModal = false"
              class="px-8 py-2.5 rounded-xl bg-emerald-600 text-white font-bold shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition-all flex items-center gap-2 text-sm uppercase tracking-wider"
            >
              <v-icon icon="mdi-account-edit-outline" size="18" />
              Edit Profile
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

.scale-fade-enter-active {
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.scale-fade-leave-active {
  transition: all 0.1s ease-in;
}
.scale-fade-enter-from,
.scale-fade-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(-10px);
}

.animate-pop {
  animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes pop {
  from { opacity: 0; transform: translateY(20px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>
