<script setup>
import { computed } from 'vue'
import XIcon from '@/Icons/X.vue'
import ArrowPathIcon from '@/Icons/ArrowPath.vue'
import CheckIcon from '@/Icons/Check.vue'
import ExclamationCircleIcon from '@/Icons/ExclamationCircle.vue'
import PureButton from '@/Components/Button/PureButton.vue'

const props = defineProps({
  fileName: {
    type: String,
    required: true
  },
  progress: {
    type: Number,
    default: 0
  },
  status: {
    type: String,
    default: 'pending' // pending, uploading, complete, error
  },
  error: {
    type: String,
    default: null
  }
})

defineEmits(['cancel', 'retry'])

const statusText = computed(() => {
  switch (props.status) {
    case 'pending':
      return 'Waiting...'
    case 'uploading':
      return `${props.progress}%`
    case 'complete':
      return 'Complete'
    case 'error':
      return props.error || 'Failed'
    default:
      return ''
  }
})

const canCancel = computed(() => {
  return props.status === 'pending' || props.status === 'uploading'
})

const canRetry = computed(() => {
  return props.status === 'error'
})

const progressBarClasses = computed(() => {
  if (props.status === 'error') {
    return 'bg-red-500'
  }
  if (props.status === 'complete') {
    return 'bg-green-500'
  }
  return 'bg-primary-500'
})
</script>
<template>
  <div class="flex items-center px-md py-sm bg-white border-b last:border-b-0 border-gray-100">
    <div class="flex-1 min-w-0">
      <div class="text-sm truncate" :title="fileName">{{ fileName }}</div>
      <div class="w-full bg-gray-200 rounded-full h-1.5 mt-xs">
        <div
          class="h-1.5 rounded-full transition-all duration-300"
          :class="progressBarClasses"
          :style="{ width: `${progress}%` }"
        ></div>
      </div>
      <div
        class="text-xs mt-xs"
        :class="{
          'text-gray-500': status === 'pending' || status === 'uploading',
          'text-lime-600': status === 'complete',
          'text-red-600': status === 'error'
        }"
      >
        <span v-if="status === 'complete'" class="flex items-center gap-1">
          <CheckIcon class="!w-3 !h-3" />
          {{ statusText }}
        </span>
        <span v-else-if="status === 'error'" class="flex items-center gap-1">
          <ExclamationCircleIcon class="!w-3 !h-3" />
          {{ statusText }}
        </span>
        <span v-else>{{ statusText }}</span>
      </div>
    </div>
    <div class="ml-sm flex items-center gap-xs">
      <PureButton v-if="canCancel" @click="$emit('cancel')">
        <template #icon>
          <XIcon class="!w-4 !h-4" />
        </template>
      </PureButton>
      <PureButton v-if="canRetry" @click="$emit('retry')">
        <template #icon>
          <ArrowPathIcon class="!w-4 !h-4" />
        </template>
      </PureButton>
    </div>
  </div>
</template>
