<script setup>
import SecondaryButton from '../../Button/SecondaryButton.vue'
import Flex from '../../Layout/Flex.vue'
import PlusSimple from '../../../Icons/PlusSimple.vue'
import Minus from '../../../Icons/Minus.vue'
import Sparkles from '../../../Icons/Sparkles.vue'

defineEmits(['selected-command'])

defineProps({
  disabled: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
  <Flex :wrap="true" class="justify-end">
    <SecondaryButton :disabled="disabled" size="xs" @click="$emit('selected-command', 'rephrase')">
      <template #icon><Sparkles /></template>
      {{ $t('ai.rephrase') }}
    </SecondaryButton>

    <SecondaryButton :disabled="disabled" size="xs" @click="$emit('selected-command', 'expand')">
      <template #icon><PlusSimple /></template>
      {{ $t('ai.expand') }}
    </SecondaryButton>

    <SecondaryButton :disabled="disabled" size="xs" @click="$emit('selected-command', 'shorten')">
      <template #icon><Minus /></template>
      {{ $t('ai.shorten') }}
    </SecondaryButton>

    <SecondaryButton :disabled="disabled" size="xs" @click="$emit('selected-command', 'simplify')">
      <template #icon><Sparkles /></template>
      {{ $t('ai.simplify') }}
    </SecondaryButton>

    <SecondaryButton
      :disabled="disabled"
      size="xs"
      @click="$emit('selected-command', 'friendly_tone')"
    >
      <template #icon><div class="text-lg">😊</div></template>
      {{ $t('ai.more_friendly') }}
    </SecondaryButton>

    <SecondaryButton
      :disabled="disabled"
      size="xs"
      @click="$emit('selected-command', 'formal_tone')"
    >
      <template #icon><div class="text-lg">🎩</div></template>
      {{ $t('ai.more_formal') }}
    </SecondaryButton>

    <SecondaryButton :disabled="disabled" size="xs" @click="$emit('selected-command', 'edgy_tone')">
      <template #icon><div class="text-lg">🤘</div></template>
      {{ $t('ai.more_edgy') }}
    </SecondaryButton>

    <SecondaryButton
      :disabled="disabled"
      size="xs"
      @click="$emit('selected-command', 'engaging_tone')"
    >
      <template #icon><div class="text-lg">🤝</div></template>
      {{ $t('ai.more_engaging') }}
    </SecondaryButton>
  </Flex>
</template>
