<script setup>
import SearchInput from '../Util/SearchInput.vue'

defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

defineEmits(['update:modelValue'])
</script>
<template>
  <div class="flex items-center">
    <SearchInput
      :model-value="modelValue.invoice_number"
      :placeholder="$t('finance.invoice_number')"
      @update:model-value="$emit('update:modelValue', { ...modelValue, invoice_number: $event })"
    />
  </div>
</template>
