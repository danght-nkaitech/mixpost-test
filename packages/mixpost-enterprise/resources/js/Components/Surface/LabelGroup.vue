<script setup>
defineProps({
  active: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
  <label
    class="border rounded-md p-sm w-full"
    :class="{ 'border-primary-500': active, 'border-gray-200': !active }"
  >
    <slot />
  </label>
</template>
