<script setup>
import VideoSolidIcon from '@/Icons/VideoSolid.vue'
import PhotoSolidIcon from '@/Icons/PhotoSolid.vue'

defineProps({
  type: {
    type: String,
    default: 'video'
  },
  widthClass: {
    type: String,
    default: 'w-28'
  },
  heightClass: {
    type: String,
    default: 'h-28'
  }
})
</script>
<template>
  <div class="relative bg-gray-200 rounded-md" :class="[widthClass, heightClass]">
    <span class="absolute w-full h-full flex items-center justify-center">
      <template v-if="type === 'video'">
        <VideoSolidIcon class="text-black" />
      </template>

      <template v-if="type === 'image'">
        <PhotoSolidIcon class="text-black" />
      </template>
    </span>

    <slot />
  </div>
</template>
