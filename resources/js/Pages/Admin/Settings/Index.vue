<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  auth_user: Object,
  settings: Object
});

// 1. Profile Form
const profileForm = useForm({
  name: props.auth_user?.name || "",
  email: props.auth_user?.email || "",
  phone: props.auth_user?.phone || "",
  image: null,
});

// 2. Password Form
const passwordForm = useForm({
  current_password: "",
  new_password: "",
  new_password_confirmation: "",
});

// 3. System Settings Form
const systemForm = useForm({
  store_name: props.settings?.store_name || "Cambo_Mart",
  currency: props.settings?.currency || "USD",
  logo: null,
});

const profileImagePreview = ref(props.auth_user?.image && props.auth_user.image !== 'null' ? props.auth_user.image : null);
const logoPreview = ref(props.settings?.logo || null);
const profileImageInput = ref(null);
const logoInput = ref(null);

// --- Profile Actions ---

function selectProfileImage() {
  profileImageInput.value.click();
}

async function updateProfileImagePreview(event) {
  const file = event.target.files[0];
  if (!file) return;

  // Local Preview
  const reader = new FileReader();
  reader.onload = (e) => { 
    profileImagePreview.value = e.target.result; 
  };
  reader.readAsDataURL(file);

  // Upload to server
  const formData = new FormData();
  formData.append('image', file);

  try {
    const response = await axios.post('/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    profileForm.image = response.data.url;
    console.log('Profile image uploaded:', response.data.url);
  } catch (error) {
    console.error("Profile image upload failed", error);
    alert('Image upload failed. Please try again.');
  }
}

function updateProfile() {
  profileForm.put(route('admin.settings.update-profile'), {
    preserveScroll: true,
    onSuccess: () => {
      alert("Profile Updated Successfully");
      // Refresh the page to update auth user data in header
      router.reload({ only: ['auth_user'] });
    }
  });
}

function updatePassword() {
  passwordForm.put(route('admin.settings.update-password'), {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
      alert("Password Updated Successfully");
    },
    onError: () => {
      alert("Password update failed. Please check your current password.");
    }
  });
}

// --- System Settings Actions ---

function selectLogo() {
  logoInput.value.click();
}

async function updateLogoPreview(event) {
  const file = event.target.files[0];
  if (!file) return;

  // Local Preview
  const reader = new FileReader();
  reader.onload = (e) => { 
    logoPreview.value = e.target.result; 
  };
  reader.readAsDataURL(file);

  // Upload to server
  const formData = new FormData();
  formData.append('image', file);

  try {
    const response = await axios.post('/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    systemForm.logo = response.data.url;
    console.log('Logo uploaded:', response.data.url);
  } catch (error) {
    console.error("Logo upload failed", error);
    alert('Logo upload failed. Please try again.');
  }
}

function updateSystemSettings() {
  systemForm.put(route('admin.settings.update-system'), {
    preserveScroll: true,
    onSuccess: () => {
      alert("System Settings Updated Successfully");
    }
  });
}
</script>

<template>
  <AdminLayout title="Settings" subtitle="Manage your account and system preferences">
    <div class="grid gap-6">
      
      <!-- Profile Settings Section -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-slate-200">
          <div class="flex items-center gap-3">
             <div class="p-2 bg-white shadow-md text-indigo-600 rounded-xl">
               <v-icon size="24">mdi-account-circle</v-icon>
             </div>
             <div>
               <h3 class="text-lg font-black text-slate-800">Profile Settings</h3>
               <p class="text-xs text-slate-500">Update your personal information and profile picture</p>
             </div>
          </div>
        </div>
        
        <div class="p-6 space-y-6">
           <!-- Profile Image Upload -->
           <div class="flex items-start gap-6 pb-6 border-b border-slate-100">
              <div class="relative">
                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 border-4 border-white shadow-lg flex items-center justify-center text-2xl font-black text-indigo-600 overflow-hidden">
                   <img v-if="profileImagePreview" :src="profileImagePreview" class="w-full h-full object-cover">
                   <span v-else>{{ profileForm.name?.charAt(0) || 'A' }}</span>
                </div>
                <div class="absolute -bottom-2 -right-2 p-1.5 bg-indigo-600 text-white rounded-full shadow-lg">
                  <v-icon size="16">mdi-camera</v-icon>
                </div>
              </div>
              
              <div class="flex-1 space-y-3">
                 <input type="file" ref="profileImageInput" class="hidden" @change="updateProfileImagePreview" accept="image/*">
                 
                 <div class="flex gap-2">
                   <button @click="selectProfileImage" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm flex items-center gap-2">
                     <v-icon size="16" color="white">mdi-upload</v-icon>
                     Upload Photo
                   </button>
                   <button v-if="profileImagePreview" @click="() => { profileImagePreview = null; profileForm.image = null; }" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-50 transition-all">
                     Remove
                   </button>
                 </div>
                 <p class="text-xs text-slate-500">JPG, PNG or GIF. Max size 2MB. Recommended 400x400px</p>
              </div>
           </div>

           <!-- Profile Form Fields -->
           <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                 <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                   <v-icon size="14">mdi-account</v-icon>
                   Full Name
                 </label>
                 <input v-model="profileForm.name" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700" placeholder="Enter your name">
                 <div v-if="profileForm.errors.name" class="text-rose-500 text-xs font-bold">{{ profileForm.errors.name }}</div>
              </div>
              
              <div class="space-y-2">
                 <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                   <v-icon size="14">mdi-email</v-icon>
                   Email Address
                 </label>
                 <input v-model="profileForm.email" type="email" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700" placeholder="your@email.com">
                 <div v-if="profileForm.errors.email" class="text-rose-500 text-xs font-bold">{{ profileForm.errors.email }}</div>
              </div>
              
              <div class="space-y-2">
                 <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                   <v-icon size="14">mdi-phone</v-icon>
                   Phone Number
                 </label>
                 <input v-model="profileForm.phone" type="tel" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700" placeholder="+1 (555) 000-0000">
              </div>
           </div>

           <div class="flex justify-end pt-4">
              <button @click="updateProfile" :disabled="profileForm.processing" class="flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all active:scale-95 shadow-lg disabled:opacity-50">
                 <v-icon size="18" color="white">mdi-content-save</v-icon>
                 {{ profileForm.processing ? 'Saving...' : 'Save Profile' }}
              </button>
           </div>
        </div>
      </div>

      <!-- Security Section -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-rose-50 to-red-50 border-b border-slate-200">
          <div class="flex items-center gap-3">
             <div class="p-2 bg-white shadow-md text-rose-600 rounded-xl">
               <v-icon size="24">mdi-shield-lock</v-icon>
             </div>
             <div>
               <h3 class="text-lg font-black text-slate-800">Security</h3>
               <p class="text-xs text-slate-500">Manage your password and security settings</p>
             </div>
          </div>
        </div>
        
        <div class="p-6">
          <div class="max-w-2xl space-y-4">
            <div class="space-y-2">
               <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                 <v-icon size="14">mdi-lock</v-icon>
                 Current Password
               </label>
               <input v-model="passwordForm.current_password" type="password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all" placeholder="Enter current password">
               <div v-if="passwordForm.errors.current_password" class="text-rose-500 text-xs font-bold">{{ passwordForm.errors.current_password }}</div>
            </div>
            
            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                 <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                   <v-icon size="14">mdi-lock-plus</v-icon>
                   New Password
                 </label>
                 <input v-model="passwordForm.new_password" type="password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all" placeholder="Enter new password">
                 <div v-if="passwordForm.errors.new_password" class="text-rose-500 text-xs font-bold">{{ passwordForm.errors.new_password }}</div>
              </div>
              
              <div class="space-y-2">
                 <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                   <v-icon size="14">mdi-lock-check</v-icon>
                   Confirm New Password
                 </label>
                 <input v-model="passwordForm.new_password_confirmation" type="password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all" placeholder="Confirm new password">
              </div>
            </div>

            <div class="flex justify-end pt-4">
               <button @click="updatePassword" :disabled="passwordForm.processing" class="flex items-center gap-2 px-6 py-3 bg-rose-600 text-white rounded-xl font-bold hover:bg-rose-700 transition-all active:scale-95 shadow-lg disabled:opacity-50">
                  <v-icon size="18" color="white">mdi-shield-check</v-icon>
                  {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
               </button>
            </div>
          </div>
        </div>
      </div>

      <!-- System Settings Section -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-50 to-green-50 border-b border-slate-200">
          <div class="flex items-center gap-3">
             <div class="p-2 bg-white shadow-md text-emerald-600 rounded-xl">
               <v-icon size="24">mdi-cog</v-icon>
             </div>
             <div>
               <h3 class="text-lg font-black text-slate-800">System Settings</h3>
               <p class="text-xs text-slate-500">Configure store name, logo, and currency</p>
             </div>
          </div>
        </div>
        
        <div class="p-6 space-y-6">
           <!-- Logo Upload -->
           <div class="flex items-start gap-6 pb-6 border-b border-slate-100">
              <div class="relative">
                <div class="w-32 h-32 rounded-2xl bg-gradient-to-br from-emerald-100 to-green-100 border-4 border-white shadow-lg flex items-center justify-center overflow-hidden">
                   <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-contain p-2">
                   <v-icon v-else size="48" class="text-emerald-400">mdi-store</v-icon>
                </div>
                <div class="absolute -bottom-2 -right-2 p-1.5 bg-emerald-600 text-white rounded-full shadow-lg">
                  <v-icon size="16">mdi-image</v-icon>
                </div>
              </div>
              
              <div class="flex-1 space-y-3">
                 <input type="file" ref="logoInput" class="hidden" @change="updateLogoPreview" accept="image/*">
                 
                 <div class="flex gap-2">
                   <button @click="selectLogo" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all shadow-sm flex items-center gap-2">
                     <v-icon size="16" color="white">mdi-upload</v-icon>
                     Upload Logo
                   </button>
                   <button v-if="logoPreview" @click="logoPreview = null" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-50 transition-all">
                     Remove
                   </button>
                 </div>
                 <p class="text-xs text-slate-500">PNG with transparent background recommended. Max size 2MB</p>
              </div>
           </div>

           <!-- System Form Fields -->
           <div class="grid gap-4 md:grid-cols-2 max-w-2xl">
              <div class="space-y-2">
                 <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                   <v-icon size="14">mdi-store</v-icon>
                   Store Name
                 </label>
                 <input v-model="systemForm.store_name" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all font-medium text-slate-700" placeholder="Cambo_Mart">
                 <div v-if="systemForm.errors.store_name" class="text-rose-500 text-xs font-bold">{{ systemForm.errors.store_name }}</div>
              </div>
              
              <div class="space-y-2">
                 <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                   <v-icon size="14">mdi-currency-usd</v-icon>
                   Currency
                 </label>
                 <select v-model="systemForm.currency" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700 cursor-pointer">
                   <option value="USD">$ USD - US Dollar</option>
                 </select>
                 <p class="text-xs text-slate-400 flex items-center gap-1">
                   <v-icon size="12">mdi-information</v-icon>
                   Currently supporting USD only
                 </p>
              </div>
           </div>

           <div class="flex justify-end pt-4">
              <button @click="updateSystemSettings" :disabled="systemForm.processing" class="flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all active:scale-95 shadow-lg disabled:opacity-50">
                 <v-icon size="18" color="white">mdi-content-save</v-icon>
                 {{ systemForm.processing ? 'Saving...' : 'Save System Settings' }}
              </button>
           </div>
        </div>
      </div>
      
    </div>
  </AdminLayout>
</template>