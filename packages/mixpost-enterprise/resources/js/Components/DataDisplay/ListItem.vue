<script setup>
defineProps({
  withClassesForLast: {
    type: Boolean,
    default: true
  },
  active: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
  <div
    :class="{
      'last:rounded-t-none last:rounded-b-lg last:border-none': withClassesForLast,
      'bg-primary-50': active,
      'hover:bg-gray-100': !active
    }"
    class="p-xs rounded-t-lg border-b hover:bg-gray-100 transition-colors ease-in-out duration-200"
  >
    <slot />
  </div>
</template>
