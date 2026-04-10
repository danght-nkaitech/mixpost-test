<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import XIcon from '@/Icons/X.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  media: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const videoRef = ref(null)

const close = () => {
  if (videoRef.value) {
    videoRef.value.pause()
    videoRef.value.removeAttribute('src')
    videoRef.value.load()
  }

  emit('close')
}

const onKeydown = e => {
  if (e.key === 'Escape' && props.show) {
    close()
  }
}

watch(
  () => props.show,
  value => {
    document.body.style.overflow = value ? 'hidden' : null

    if (!value) {
      close()
    }
  }
)

onMounted(() => document.addEventListener('keydown', onKeydown))

onUnmounted(() => {
  document.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = null
})
</script>

<template>
  <teleport to="body">
    <transition leave-active-class="duration-200">
      <div v-show="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <transition
          enter-active-class="ease-out duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="ease-in duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-show="show" class="absolute inset-0 bg-black/80" @click="close" />
        </transition>

        <transition
          enter-active-class="ease-out duration-300"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="ease-in duration-200"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="show && media" class="relative z-10 max-w-[90vw] max-h-[90vh] flex flex-col">
            <button
              class="absolute -top-sm -right-sm w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-colors cursor-pointer z-20"
              @click="close"
            >
              <XIcon class="!w-4 !h-4 text-gray-800" />
            </button>

            <template v-if="media">
              <template v-if="media.is_video">
                <video
                  ref="videoRef"
                  :key="media.url"
                  :src="media.url"
                  controls
                  autoplay
                  class="max-w-[90vw] max-h-[85vh] rounded-lg shadow-2xl"
                />
              </template>
              <template v-else>
                <img
                  :src="media.url || media.thumb_url"
                  :alt="media.name"
                  class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg shadow-2xl"
                />
              </template>

              <div class="mt-xs text-center text-sm text-white/70 truncate px-md">
                {{ media.name }}
              </div>
            </template>
          </div>
        </transition>
      </div>
    </transition>
  </teleport>
</template>
