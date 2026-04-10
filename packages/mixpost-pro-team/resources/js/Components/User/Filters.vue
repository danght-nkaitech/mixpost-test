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
      :model-value="modelValue.keyword"
      :placeholder="$t('general.search')"
      @update:model-value="$emit('update:modelValue', { ...modelValue, keyword: $event })"
    />
  </div>
</template>
