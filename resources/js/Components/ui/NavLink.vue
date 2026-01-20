<script setup>
import { useRoute, RouterLink } from "vue-router";
import { computed } from "vue";

const props = defineProps({
  to: { type: [String, Object], required: true },
  className: { type: String, default: "" },
  activeClassName: { type: String, default: "text-blue-500 font-bold" },
  exact: { type: Boolean, default: false }
});

const route = useRoute();

const isActive = computed(() => {
  if (typeof props.to === "string") {
    return props.exact ? route.path === props.to : route.path.startsWith(props.to);
  }
  return false;
});

const classes = computed(() => {
  return props.className + (isActive.value ? ` ${props.activeClassName}` : "");
});
</script>

<template>
  <RouterLink :to="to" :class="classes">
    <slot />
  </RouterLink>
</template>
