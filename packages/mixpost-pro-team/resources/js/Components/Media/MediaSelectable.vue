<script setup>
import CheckIcon from '@/Icons/Check.vue'
defineProps({
  active: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])
</script>
<template>
  <div class="w-full relative cursor-pointer box-border" @click="$emit('click')">
    <slot></slot>
    <div
      class="absolute flex items-center justify-center top-0 right-0 mr-md mt-md w-7 h-7 rounded-full border border-white"
      :class="{ 'bg-primary-500': active, 'bg-black/20': !active }"
    >
      <CheckIcon v-if="active" class="text-white" />
    </div>
  </div>
</template>
