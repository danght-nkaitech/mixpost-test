<script setup>
import { ref, computed } from 'vue'
import CheckIcon from '@/Icons/Check.vue'
import ExclamationIcon from '@/Icons/Exclamation.vue'
import XIcon from '@/Icons/X.vue'

const emit = defineEmits(['close'])

const props = defineProps({
  variant: {
    type: String,
    default: 'info'
  },
  closeable: {
    type: Boolean,
    default: true
  }
})

const show = ref(true)

const close = () => {
  show.value = false
  emit('close')
}

const variantIcon = computed(() => {
  return {
    success: CheckIcon,
    warning: ExclamationIcon,
    error: ExclamationIcon,
    info: ExclamationIcon
  }[props.variant]
})

const variantColorClasses = computed(() => {
  return {
    info: 'bg-cyan-100 text-cyan-600',
    success: 'bg-lime-100 text-lime-600',
    warning: 'bg-orange-100 text-orange-600',
    error: 'bg-red-100 text-red-600'
  }[props.variant]
})
</script>
<template>
  <div v-if="show" class="flex p-md rounded-md bg-alert">
    <div class="w-full flex items-center justify-between">
      <div class="flex items-center">
        <div>
          <div
            :class="variantColorClasses"
            class="w-8 h-8 rounded-full flex items-center justify-center mr-sm rtl:mr-0 rtl:ml-sm"
          >
            <component :is="variantIcon" />
          </div>
        </div>
        <div class="text-alert-context">
          <slot />
        </div>
      </div>
      <template v-if="closeable">
        <button class="ml-2xl" @click="close">
          <XIcon class="text-gray-200" />
        </button>
      </template>
    </div>
  </div>
</template>
