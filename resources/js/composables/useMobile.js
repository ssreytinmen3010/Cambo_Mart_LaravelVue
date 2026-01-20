import { ref, onMounted, onUnmounted } from "vue";

// Define the breakpoint for mobile
const MOBILE_BREAKPOINT = 768;

export function useMobile() {
  // Reactive state
  const isMobile = ref(window.innerWidth < MOBILE_BREAKPOINT);

  // Function to update isMobile when window is resized
  function onResize() {
    isMobile.value = window.innerWidth < MOBILE_BREAKPOINT;
  }

  // Listen to window resize when component mounts
  onMounted(() => {
    window.addEventListener("resize", onResize);
  });

  // Remove listener when component unmounts
  onUnmounted(() => {
    window.removeEventListener("resize", onResize);
  });

  return isMobile;
}
