<script setup>
import OneImage from '@/Components/PostPreview/Bluesky/Gallery/OneImage.vue'
import TwoImage from '@/Components/PostPreview/Bluesky/Gallery/TwoImage.vue'
import ThreeImage from '@/Components/PostPreview/Bluesky/Gallery/ThreeImage.vue'
import FourImage from '@/Components/PostPreview/Bluesky/Gallery/FourImage.vue'
import Video from '@/Components/PostPreview/Bluesky/Gallery/Video.vue'

defineProps({
  media: {
    type: Array,
    required: true
  }
})
</script>
<template>
  <div v-if="media.length">
    <div v-if="media[0].is_video" class="overflow-hidden rounded-xl">
      <Video :media="media[0]" />
    </div>

    <div v-else class="overflow-hidden rounded-xl">
      <OneImage v-if="media.length === 1" :media="media[0]" />
      <TwoImage v-if="media.length === 2" :media="media" />
      <ThreeImage v-if="media.length === 3" :media="media" />
      <FourImage v-if="media.length > 3" :media="media" />
    </div>
  </div>
</template>
