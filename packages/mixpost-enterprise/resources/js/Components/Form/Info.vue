<script setup>
import ExclamationIcon from '@/Icons/Exclamation.vue'
</script>
<template>
  <p class="flex items-start text-indigo-500 leading-loose text-sm">
    <span class="mr-xs"><ExclamationIcon class="w-5! !-5" /></span>
    <span><slot /></span>
  </p>
</template>
