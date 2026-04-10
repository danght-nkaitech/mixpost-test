<script setup>
import { computed } from 'vue'
import CheckIcon from '@/Icons/Check.vue'
import ExclamationIcon from '@/Icons/Exclamation.vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'info'
  }
})

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
    info: 'text-cyan-600',
    success: 'text-lime-600',
    warning: 'text-orange-600',
    error: 'text-red-600'
  }[props.variant]
})
</script>
<template>
  <div class="flex items-center">
    <div>
      <div
        :class="variantColorClasses"
        class="w-8 h-8 flex items-center justify-center mr-xs rtl:mr-0 rtl:ml-xs"
      >
        <component :is="variantIcon" />
      </div>
    </div>
    <div :class="variantColorClasses">
      <slot />
    </div>
  </div>
</template>
