<script setup>
import Header from '@/Components/User/Header.vue';
import Footer from '@/Components/User/Footer.vue';
import PromotionPopup from '@/Components/User/PromotionPopup.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background">
        <Header />
        <main class="flex-1">
            <slot />
        </main>
        <Footer />
        <PromotionPopup />
    </div>
</template>
