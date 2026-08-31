import { ref, readonly } from 'vue';

const toasts = ref([]);

let toastId = 0;

export function useToast() {
  const addToast = ({ message, type = 'success', title = '', duration = 4000 }) => {
    const id = ++toastId;
    const newToast = { id, message, type, title };
    toasts.value.push(newToast);

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }

    return id;
  };

  const removeToast = (id) => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index !== -1) {
      toasts.value.splice(index, 1);
    }
  };

  const success = (message, title = 'Berhasil') => addToast({ message, title, type: 'success' });
  const error = (message, title = 'Terjadi Kesalahan') => addToast({ message, title, type: 'error' });
  const warning = (message, title = 'Peringatan') => addToast({ message, title, type: 'warning' });
  const info = (message, title = 'Informasi') => addToast({ message, title, type: 'info' });

  return {
    toasts: readonly(toasts),
    addToast,
    removeToast,
    success,
    error,
    warning,
    info,
  };
}
