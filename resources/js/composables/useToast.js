import { reactive, readonly } from "vue";

const TOAST_LIMIT = 1; // Only 1 toast at a time
const TOAST_REMOVE_DELAY = 5000; // 5 seconds auto-remove

let count = 0;

function genId() {
  count = (count + 1) % Number.MAX_SAFE_INTEGER;
  return count.toString();
}

// Reactive state for all toasts
const state = reactive({
  toasts: []
});

// Track timeouts for auto-dismiss
const toastTimeouts = new Map();

function addToRemoveQueue(toastId) {
  if (toastTimeouts.has(toastId)) return;

  const timeout = setTimeout(() => {
    toastTimeouts.delete(toastId);
    removeToast(toastId);
  }, TOAST_REMOVE_DELAY);

  toastTimeouts.set(toastId, timeout);
}

// Add a new toast
function addToast({ title, description, action }) {
  const id = genId();
  const newToast = {
    id,
    title,
    description,
    action,
    open: true
  };

  state.toasts.unshift(newToast);
  if (state.toasts.length > TOAST_LIMIT) state.toasts.pop();

  addToRemoveQueue(id);

  return {
    id,
    dismiss: () => dismissToast(id),
    update: (props) => updateToast(id, props)
  };
}

// Dismiss a toast manually
function dismissToast(toastId) {
  const toast = state.toasts.find(t => t.id === toastId);
  if (toast) {
    toast.open = false;
    addToRemoveQueue(toastId);
  }
}

// Remove toast from state
function removeToast(toastId) {
  const index = state.toasts.findIndex(t => t.id === toastId);
  if (index !== -1) state.toasts.splice(index, 1);
}

// Update toast content
function updateToast(toastId, props) {
  const toast = state.toasts.find(t => t.id === toastId);
  if (toast) Object.assign(toast, props);
}

// Composable function
export function useToast() {
  return {
    toasts: readonly(state.toasts),
    toast: addToast,
    dismiss: dismissToast,
    update: updateToast
  };
}
