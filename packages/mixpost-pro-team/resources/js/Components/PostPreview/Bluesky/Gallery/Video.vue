<script setup>
import { ref } from 'vue'
import PlayIcon from '@/Icons/Play.vue'
import Placeholder from '@/Components/Media/Placeholder.vue'

const isOpen = ref(false)

defineProps({
  media: {
    type: Object,
    required: true
  }
})
</script>
<template>
  <div class="relative">
    <div class="w-full h-full absolute flex items-center justify-center">
      <template v-if="!isOpen">
        <button
          class="w-16 h-16 border-2 border-white rounded-full flex items-center justify-center text-white bg-twitter"
          @click="isOpen = true"
        >
          <PlayIcon class="w-10! h-10!" />
        </button>
      </template>
    </div>

    <template v-if="media.thumb_url && !isOpen">
      <img :src="media.thumb_url" draggable="false" class="rounded-xl h-full w-full" alt="Image" />
    </template>

    <template v-if="!media.thumb_url && !isOpen">
      <Placeholder width-class="w-full" type="video" />
    </template>

    <video v-if="isOpen" class="w-auto h-full mx-auto" controls autoplay media="">
      <source :src="media.url" :type="media.mime_type" />
      {{ $t('error.browser_video_unsupported') }}
    </video>
  </div>
</template>
