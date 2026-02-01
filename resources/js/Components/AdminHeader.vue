<template>
  <header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 backdrop-blur-md px-4 md:px-8">
    
    <div class="flex flex-col">
      <h1 class="text-lg font-bold tracking-tight text-slate-900 leading-tight">{{ title }}</h1>
      <p v-if="subtitle" class="hidden sm:block text-xs font-medium text-slate-500 uppercase tracking-wider">
        {{ subtitle }}
      </p>
    </div>

    <div class="flex items-center gap-3 md:gap-6">
      
      <div v-if="!isMobile" class="relative group">
        <!-- <v-icon 
          class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors"
          size="20"
        >
          mdi-magnify
        </v-icon> -->
        <input
          type="search"
          placeholder="Search records..."
          class="w-64 pl-10 pr-4 h-10 border border-slate-200 rounded-xl bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
        />
      </div>

      <div class="flex items-center border-l border-slate-200 pl-4 md:pl-6 gap-2">
        
        <button class="relative p-2 rounded-xl text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
          <v-icon size="24">mdi-bell-outline</v-icon>
          <span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-red-500 border-2 border-white">
            <span class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-75"></span>
          </span>
        </button>

        <div class="relative">
          <button 
            @click="open = !open" 
            :class="[
              'flex items-center gap-3 p-1.5 rounded-xl transition-all border',
              open ? 'border-emerald-500 bg-emerald-50' : 'border-transparent hover:bg-slate-50'
            ]"
          >
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xs font-bold shadow-sm shadow-emerald-200">
              AD
            </div>
            
            <div class="hidden md:flex flex-col items-start leading-none text-left">
              <span class="text-sm font-semibold text-slate-700">Admin User</span>
              <span class="text-[10px] text-emerald-600 font-bold uppercase">Pro Plan</span>
            </div>
            
            <v-icon size="18" :class="['text-slate-400 transition-transform', open ? 'rotate-180' : '']">
              mdi-chevron-down
            </v-icon>
          </button>

          <transition 
            enter-active-class="transition duration-100 ease-out" 
            enter-from-class="transform scale-95 opacity-0" 
            enter-to-class="transform scale-100 opacity-100" 
            leave-active-class="transition duration-75 ease-in" 
            leave-from-class="transform scale-100 opacity-100" 
            leave-to-class="transform scale-95 opacity-0"
          >
            <div v-if="open" class="absolute right-0 mt-2 w-56 origin-top-right rounded-2xl border border-slate-100 bg-white shadow-xl shadow-slate-200/50 py-2 z-50">
              <div class="px-4 py-3 border-b border-slate-50 mb-1">
                <p class="text-xs text-slate-400 font-medium">Signed in as</p>
                <p class="text-sm font-bold text-slate-700 truncate">admin@cambomart.com</p>
              </div>
              
              <ul class="px-2 space-y-0.5">
                <li class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors">
                  <v-icon size="18">mdi-account-circle-outline</v-icon> Profile
                </li>
                <li class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors">
                  <v-icon size="18">mdi-cog-outline</v-icon> Settings
                </li>
                <div class="h-px bg-slate-50 my-1"></div>
                <li @click="logout" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-500 hover:bg-red-50 cursor-pointer transition-colors">
                  <v-icon size="18">mdi-logout</v-icon> Sign Out
                </li>
              </ul>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from "vue";
import { router } from '@inertiajs/vue3';

// Assuming you have this composable; otherwise replace with a reactive window listener
const isMobile = ref(false); 
const open = ref(false);

defineProps({
  title: { type: String, default: "Dashboard" },
  subtitle: { type: String, default: "" }
});

const logout = () => {
  router.post('/logout');
};
</script>