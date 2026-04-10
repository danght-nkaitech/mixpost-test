<script setup>
import { capitalize } from 'lodash'

defineProps({
  sourceUrl: {
    type: String,
    required: true
  },
  provider: {
    type: String,
    required: true
  },
  creditUrl: {
    type: String,
    required: true
  },
  authorName: {
    type: String,
    required: true
  }
})
</script>
<template>
  <div class="text-xs text-gray-500">
    <div>
      {{ $t('media.image_source') }}:
      <a :href="sourceUrl" target="_blank" class="link">{{ capitalize(provider) }}</a>
    </div>
    <div>
      {{ $t('media.author') }}:
      <a :href="creditUrl" target="_blank" class="link">{{ authorName }}</a>
    </div>
  </div>
</template>
