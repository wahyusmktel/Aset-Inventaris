import { ref, readonly } from 'vue';

const alertState = ref({
  isOpen: false,
  type: 'warning', // 'warning' | 'error' | 'success' | 'info' | 'question'
  title: '',
  message: '',
  confirmText: 'Ya, Lanjutkan',
  cancelText: 'Batal',
  showCancel: true,
  loading: false,
  onConfirm: null,
  onCancel: null,
});

export function useAlert() {
  const confirm = ({
    title = 'Konfirmasi Tindakan',
    message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    type = 'warning',
    confirmText = 'Ya, Lanjutkan',
    cancelText = 'Batal',
    showCancel = true,
  }) => {
    return new Promise((resolve) => {
      alertState.value = {
        isOpen: true,
        type,
        title,
        message,
        confirmText,
        cancelText,
        showCancel,
        loading: false,
        onConfirm: () => {
          alertState.value.isOpen = false;
          resolve(true);
        },
        onCancel: () => {
          alertState.value.isOpen = false;
          resolve(false);
        },
      };
    });
  };

  const close = () => {
    alertState.value.isOpen = false;
  };

  return {
    alertState: readonly(alertState),
    confirm,
    close,
  };
}
