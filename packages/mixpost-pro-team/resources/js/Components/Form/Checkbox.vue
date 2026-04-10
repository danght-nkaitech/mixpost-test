<script setup>
import { computed } from 'vue'

const emit = defineEmits(['update:checked'])

const props = defineProps({
  checked: {
    type: [Array, Boolean], // TODO: Do we really need Array here?
    default: false
  },
  value: {
    type: [String, Number, Boolean],
    default: null
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
    type="checkbox"
    :value="value"
    class="rounded-md w-5 h-5 border-gray-200 text-primary-500 shadow-xs cursor-pointer focus:border-default focus:ring-3 focus:ring-primary-200/50 disabled:border-gray-100 disabled:cursor-not-allowed"
  />
</template>
