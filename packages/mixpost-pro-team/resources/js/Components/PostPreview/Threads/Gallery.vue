<script setup>
import { computed } from 'vue'
import Placeholder from '@/Components/Media/Placeholder.vue'

const props = defineProps({
  media: {
    type: Array,
    required: true
  }
})

const limitedMedia = computed(() => props.media.slice(0, 3))
</script>
<template>
  <div v-if="media.length" class="w-full">
    <!-- Display a single image -->
    <div v-if="media.length === 1" class="w-full">
      <template v-if="media[0].thumb_url">
        <img
          :src="media[0].thumb_url"
          alt="Gallery Image"
          class="w-full rounded-md object-cover object-left"
        />
      </template>

      <template v-if="!media[0].thumb_url">
        <Placeholder width-class="w-full" type="video" />
      </template>
    </div>

    <!-- Display two images -->
    <div v-else-if="media.length === 2" class="grid grid-cols-2 overflow-hidden gap-2 h-[200px]">
      <img
        v-for="(image, index) in media"
        :key="index"
        :src="image.thumb_url"
        alt="Gallery Image"
        class="w-full h-full rounded-md object-cover object-left"
      />
    </div>

    <!-- Carousel impression with three images -->
    <div v-else class="flex gap-xs overflow-hidden h-[200px]">
      <!-- Render the first two images fully -->
      <div
        v-for="(image, index) in limitedMedia"
        :key="index"
        :class="[
          'shrink-0 rounded-md overflow-hidden h-full',
          index === limitedMedia.length - 1 ? 'w-[15%]' : 'w-[42.5%]'
        ]"
      >
        <img
          :src="image.thumb_url"
          alt="Gallery Image"
          class="w-full h-full object-cover object-left"
        />
      </div>
    </div>
  </div>
</template>
