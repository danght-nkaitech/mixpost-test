<script setup>
import MediaFile from '../Media/MediaFile.vue'
import EditorReadOnly from '../Package/EditorReadOnly.vue'

defineProps({
  template: {
    type: Object,
    required: true
  }
})
</script>
<template>
  <div class="flex items-center justify-between">
    <div class="w-full font-medium">{{ template.name }}</div>

    <slot name="action" />
  </div>

  <template v-if="template.content.length">
    <div class="bg-gray-50 mt-lg p-md rounded-lg">
      <template v-if="template.content[0].body">
        <EditorReadOnly :value="template.content[0].body" />
      </template>

      <template v-if="template.content[0].media.length">
        <div class="flex flex-wrap gap-xs mt-xs">
          <template v-for="itemMedia in template.content[0].media" :key="itemMedia.id">
            <div>
              <MediaFile
                :media="itemMedia"
                img-height="sm"
                :img-width-full="false"
                :show-caption="false"
              />
            </div>
          </template>
        </div>
      </template>
    </div>
  </template>
</template>
