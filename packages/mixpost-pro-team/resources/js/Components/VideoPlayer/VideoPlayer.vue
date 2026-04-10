<script setup>
import { nextTick, onBeforeUnmount, ref } from 'vue'
import Play from '@/Icons/Play.vue'

defineProps({
  src: {
    required: true,
    type: String
  },
  customThumbSrc: {
    default: '',
    type: String
  }
})

const emit = defineEmits(['play', 'stop'])

const player = ref(null)
const showVideo = ref(true)
const isPlaying = ref(false)

const stop = () => {
  if (!player.value) return

  player.value.pause()
  player.value.removeAttribute('src')
  player.value.load()
  isPlaying.value = false
  showVideo.value = false

  nextTick(() => {
    showVideo.value = true
  })
}

const playStop = () => {
  if (isPlaying.value) {
    stop()
    emit('stop')
    return
  }

  nextTick(() => {
    player.value.play()
  })

  isPlaying.value = true
  emit('play')
}

onBeforeUnmount(() => {
  stop()
})
</script>
<template>
  <div class="w-full h-full" @click="playStop">
    <div class="z-10 w-full h-full absolute flex items-center justify-center">
      <button
        v-if="!isPlaying"
        class="w-16 h-16 border-2 border-white rounded-full flex items-center justify-center text-white bg-black/50"
      >
        <Play class="w-10! h-10!" />
      </button>
    </div>
    <template v-if="showVideo">
      <video
        ref="player"
        :src="src"
        preload="metadata"
        aria-label="video-player"
        class="w-full h-full object-cover"
        :poster="customThumbSrc"
      />
    </template>
  </div>
</template>
