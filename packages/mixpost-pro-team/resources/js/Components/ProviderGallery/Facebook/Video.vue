<script setup>
import { ref } from 'vue'
import Box from '@/Components/Layout/Box.vue'
import CoverStyle from '@/Components/DataDisplay/CoverStyle.vue'
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
  <Box>
    <div class="w-full h-full absolute flex items-center justify-center">
      <template v-if="!isOpen">
        <button
          class="w-16 h-16 border-2 border-white rounded-full flex items-center justify-center text-white"
          @click="isOpen = true"
        >
          <PlayIcon class="w-10! h-10!" />
        </button>
      </template>
    </div>

    <template v-if="media.thumb_url">
      <CoverStyle v-if="!isOpen" :src="media.thumb_url" alt="Image" />
    </template>

    <template v-if="!media.thumb_url && !isOpen">
      <Placeholder width-class="w-full" type="video" />
    </template>

    <video v-if="isOpen" class="w-auto h-full mx-auto" controls autoplay media="">
      <source :src="media.url" :type="media.mime_type" />
      {{ $t('error.browser_video_unsupported') }}
    </video>
  </Box>
</template>
