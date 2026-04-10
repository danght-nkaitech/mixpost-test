<script setup>
defineProps({
  classHeight: {
    type: String,
    default: 'h-48'
  }
})
</script>

<template>
  <div :class="classHeight" class="PreloaderSkeleton" />
</template>

<style scoped>
.PreloaderSkeleton {
  background: #eee;
  background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
  border-radius: 5px;
  background-size: 200% 100%;
  animation: 1.5s shine linear infinite;
}

@keyframes shine {
  to {
    background-position-x: -200%;
  }
}
</style>
