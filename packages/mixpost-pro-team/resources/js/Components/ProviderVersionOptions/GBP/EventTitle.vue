<script setup>
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Label from '@/Components/Form/Label.vue'
import Input from '@/Components/Form/Input.vue'

defineProps({
  options: {
    type: Object,
    required: true
  },
  labelName: {
    type: String,
    default: ''
  }
})
</script>
<template>
  <HorizontalGroup>
    <template #title>
      <Label for="event_title">{{ labelName || $t('service.gbp.event_title') }}</Label>
    </template>

    <Input id="event_title" v-model="options.event_title" />
  </HorizontalGroup>
</template>
