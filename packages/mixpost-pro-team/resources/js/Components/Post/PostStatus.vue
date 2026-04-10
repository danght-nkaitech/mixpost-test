<script setup>
import { computed } from 'vue'
import {
  DRAFT,
  PUBLISHED,
  PUBLISHING,
  SCHEDULED,
  NEEDS_APPROVAL,
  FAILED
} from '@/Constants/PostStatus'

const props = defineProps({
  value: {
    type: String,
    required: true
  },
  showName: {
    type: Boolean,
    required: false,
    default: true
  }
})

const classNames = computed(() => {
  return (
    {
      [DRAFT]: 'bg-gray-500',
      [PUBLISHED]: 'bg-lime-500',
      [PUBLISHING]: 'bg-violet-500',
      [SCHEDULED]: 'bg-cyan-500',
      [NEEDS_APPROVAL]: 'bg-orange-500',
      [FAILED]: 'bg-red-500'
    }[props.value] || 'bg-gray-500'
  )
})
</script>
<template>
  <div class="flex items-center">
    <div
      v-tooltip="`${showName ? '' : $t(`post.${value}`)}`"
      :class="[classNames]"
      class="w-4 h-4 rounded-full"
    ></div>
    <div v-if="showName" class="ml-xs rtl:ml-0 rtl:mr-xs">{{ $t(`post.${value}`) }}</div>
  </div>
</template>
