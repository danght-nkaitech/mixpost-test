<script setup>
import { computed } from 'vue'

const emit = defineEmits(['update:checked'])

const props = defineProps({
  checked: {
    type: [Boolean, String, Number],
    default: false
  },
  value: {
    type: [Boolean, String, Number],
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const proxyChecked = computed({
  get() {
    return props.checked
  },

  set(val) {
    emit('update:checked', val)
  }
})
</script>

<template>
  <input
    v-model="proxyChecked"
    type="radio"
    :value="value"
    :disabled="disabled"
    class="rounded-full w-5 h-5 border-gray-200 text-primary-500 shadow-xs focus:border-primary-200 focus:ring-3 focus:ring-primary-200/50 disabled:border-gray-100 disabled:cursor-not-allowed"
  />
</template>
