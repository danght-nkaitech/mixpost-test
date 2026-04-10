<script setup>
defineProps({
  active: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
  <button
    type="button"
    class="hover:text-primary-500 transition-colors ease-in-out duration-200"
    :class="{ 'text-stone-800': !active, 'text-primary-500': active }"
  >
    <slot />
  </button>
</template>
