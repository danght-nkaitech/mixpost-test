<script setup>
import Image from '@/Components/ProviderGallery/Pinterest/Image.vue'
import VideoPlayer from '@/Components/VideoPlayer/VideoPlayer.vue'

defineProps({
  media: {
    type: Array,
    required: true
  }
})
</script>
<template>
  <div v-if="media.length">
    <div class="overflow-hidden rounded-3xl">
      <div v-if="media[0].is_video" class="relative">
        <VideoPlayer
          :key="media[0].video_custom_thumb_url"
          :src="media[0].url"
          :custom-thumb-src="media[0].video_custom_thumb_url"
        />
      </div>

      <Image v-else :media="media[0]" />
    </div>
  </div>
</template>
