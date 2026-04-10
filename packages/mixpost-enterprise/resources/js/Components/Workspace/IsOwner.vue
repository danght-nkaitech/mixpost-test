<script setup>
import Checkbox from '../Form/Checkbox.vue'

defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const emits = defineEmits(['update:modelValue'])

const update = val => {
  emits('update:modelValue', val)
}
</script>
<template>
  <label>
    <Checkbox :checked="modelValue" :value="false" @update:checked="update" />
    <span class="ml-xs">{{ $t('general.owner') }}</span>
  </label>
</template>
