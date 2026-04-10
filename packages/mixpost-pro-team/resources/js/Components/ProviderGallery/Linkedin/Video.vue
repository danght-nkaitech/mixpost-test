<script setup>
import { ref } from 'vue'
import PlayIcon from '@/Icons/Play.vue'

const isOpen = ref(false)

defineProps({
  media: {
    type: Object,
    required: true
  }
})
</script>
<template>
  <div class="h-0 w-full block relative bg-black" :style="{ paddingTop: '125%' }">
    <div class="top-0 left-0 w-full h-full absolute block">
      <div
        class="blur-md w-full h-full absolute"
        :style="{ backgroundImage: `url(${media.video_custom_thumb_url || media.thumb_url})` }"
      />
      <div class="w-full h-full">
        <div class="w-full h-full inset-0 relative">
          <div class="w-full h-full absolute flex items-center justify-center">
            <template v-if="!isOpen">
              <button
                class="w-16 h-16 rounded-full flex items-center justify-center text-white bg-black bg-opacity-75"
                @click="isOpen = true"
              >
                >
                <PlayIcon class="w-10! h-10!" />
              </button>
            </template>
          </div>

          <template v-if="!isOpen && (media.video_custom_thumb_url || media.thumb_url)">
            <img
              :src="media.video_custom_thumb_url || media.thumb_url"
              draggable="false"
              class="h-full mx-auto"
              alt="Image"
            />
          </template>

          <video v-if="isOpen" class="w-auto h-full mx-auto" controls autoplay media="">
            <source :src="media.url" :type="media.mime_type" />
            {{ $t('error.browser_video_unsupported') }}
          </video>
        </div>
      </div>
    </div>
  </div>
</template>
