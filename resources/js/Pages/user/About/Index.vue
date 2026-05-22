<script setup>
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Leaf, Users, Truck, Award } from 'lucide-vue-next';

const stats = [
  { icon: Leaf, k: '100%', v: 'Naturally sourced' },
  { icon: Users, k: '12.4K+', v: 'Happy families' },
  { icon: Truck, k: 'Same-day', v: 'Phnom Penh delivery' },
  { icon: Award, k: '80+', v: 'Local brands' },
];
</script>

<template>
  <Head>
    <title>About CamboMart</title>
    <meta
      name="description"
      content="Cambodia's natural marketplace — connecting families with locally sourced fresh food."
    />
    <meta property="og:title" content="About CamboMart" />
    <meta property="og:description" content="Cambodia's natural marketplace." />
  </Head>

  <UserLayout>
    <div class="container mx-auto px-4 py-12">
      <p class="text-xs text-muted-foreground">
        <Link href="/" class="hover:text-primary">Home</Link> / About
      </p>

      <div class="mt-4 grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <span class="text-xs font-semibold uppercase tracking-wider text-primary">Our story</span>
          <h1 class="mt-3 text-4xl md:text-5xl font-bold leading-tight">
            A natural marketplace for <span class="text-gradient-brand">modern Cambodia</span>
          </h1>
          <p class="mt-5 text-muted-foreground leading-relaxed">
            CamboMart was founded in Phnom Penh to bring honest, naturally sourced food to families
            across Cambodia. We partner directly with local farmers and artisans to make fresh
            ingredients more accessible — without the markups.
          </p>
          <p class="mt-3 text-muted-foreground leading-relaxed">
            Today we serve 12,400+ households, support 80+ local brands, and deliver every order with
            care.
          </p>
        </div>

        <div class="aspect-[4/3] rounded-3xl overflow-hidden shadow-card">
          <img
            src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=900&q=80"
            alt="Fresh organic produce market"
            class="h-full w-full object-cover"
          />
        </div>
      </div>

      <div class="mt-16 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="stat in stats"
          :key="stat.v"
          class="rounded-3xl bg-card border border-border/60 p-6 text-center shadow-soft"
        >
          <div
            class="h-12 w-12 mx-auto rounded-2xl bg-primary/10 text-primary grid place-items-center"
          >
            <component :is="stat.icon" class="h-5 w-5" />
          </div>
          <p class="mt-3 text-3xl font-bold text-gradient-brand">{{ stat.k }}</p>
          <p class="text-sm text-muted-foreground">{{ stat.v }}</p>
        </div>
      </div>

      <div class="mt-16 rounded-[2rem] bg-primary-soft/40 p-10 md:p-14 text-center">
        <h2 class="text-3xl font-bold">Our promise</h2>
        <p class="mt-3 max-w-2xl mx-auto text-muted-foreground">
          Fresh quality. Fair prices. Faster delivery. We're building a marketplace Cambodians are
          proud to call their own.
        </p>
      </div>
    </div>
  </UserLayout>
</template>
