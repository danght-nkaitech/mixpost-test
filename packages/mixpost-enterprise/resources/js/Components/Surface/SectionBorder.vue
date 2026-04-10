<script setup>
defineProps({
  contrasted: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
  <div class="hidden sm:block">
    <div class="py-lg">
      <div
        class="border-t"
        :class="{ 'border-gray-100': !contrasted, 'border-gray-200': contrasted }"
      ></div>
    </div>
  </div>
</template>
