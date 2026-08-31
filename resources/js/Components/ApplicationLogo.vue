<script setup>
defineProps({
  className: {
    type: String,
    default: 'w-10 h-10',
  },
});
</script>

<template>
  <img
    src="/images/telkom-schools-logo.png"
    alt="Logo Telkom Schools"
    :class="className"
    class="object-contain"
  />
</template>
