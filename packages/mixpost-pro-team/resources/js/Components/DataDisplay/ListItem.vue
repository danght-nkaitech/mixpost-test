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
      'last:border-none': withClassesForLast,
      'bg-primary-50': active,
      'hover:bg-gray-100': !active
    }"
    class="p-xs first:rounded-t-lg last:rounded-b-lg border-b hover:bg-gray-100 transition-colors ease-in-out duration-200"
  >
    <slot />
  </div>
</template>
