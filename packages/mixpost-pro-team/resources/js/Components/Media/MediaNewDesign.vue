<script setup>
import { ref, computed } from 'vue'
import MediaAdobeExpress from '@/Components/Media/MediaAdobeExpress.vue'

const emit = defineEmits(['close', 'insert', 'selectMediaInMediaLibrary'])

const adobeExpressProperties = ref()

const selected = computed(() => adobeExpressProperties.value?.selected)

defineExpose({ selected })
</script>
<template>
  <MediaAdobeExpress
    v-if="$page.props.is_configured_service.adobe_express"
    ref="adobeExpressProperties"
    @insert="emit('insert')"
    @select-media-in-media-library="media => emit('selectMediaInMediaLibrary', media)"
    @close="emit('close')"
  />
</template>
