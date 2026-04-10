<script setup>
import HorizontalGroup from '../../Layout/HorizontalGroup.vue'
import Input from '../../Form/Input.vue'

defineProps({
  modelValue: {
    type: [Number, String],
    required: true
  }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <HorizontalGroup>
    <template #title>
      <div class="font-medium">{{ $t('general.count') }}</div>
    </template>

    <div class="w-24">
      <template v-if="!$attrs.readOnly">
        <Input
          type="number"
          :value="modelValue"
          class="w-full"
          @update:model-value="$emit('update:modelValue', $event)"
        />
      </template>
      <template v-else>
        {{ modelValue }}
      </template>
    </div>
  </HorizontalGroup>
</template>
