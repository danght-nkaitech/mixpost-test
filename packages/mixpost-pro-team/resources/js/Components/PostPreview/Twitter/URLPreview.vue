<script setup>
import usePostURLMeta from '../../../Composables/usePostURLMeta'
import { computed } from 'vue'
import PreloaderSkeleton from '../../Util/PreloaderSkeleton.vue'

const props = defineProps({
  url: {
    type: String,
    required: true
  }
})

const { getURLMeta } = usePostURLMeta()

const getHostname = () => {
  return new URL(props.url).hostname
}

const meta = computed(() => {
  const item = getURLMeta(props.url)

  if (!item) {
    return null
  }

  return {
    isLoading: item.isLoading,
    data: item.isLoading
      ? null
      : {
          image: item.data.twitter.image ? item.data.twitter.image : item.data.default.image,
          title: item.data.twitter.title ? item.data.twitter.title : item.data.default.title,
          description: item.data.twitter.description
            ? item.data.twitter.description
            : item.data.default.description
        },
    error: item.error
  }
})
</script>

<template>
  <div v-if="meta">
    <template v-if="meta.isLoading">
      <PreloaderSkeleton />
    </template>

    <template v-if="!meta.isLoading && meta.data.image">
      <div class="overflow-hidden">
        <div class="relative">
          <img :src="meta.data.image" alt="Twitter URL Preview" class="rounded-2xl" />

          <div class="absolute bottom-md px-md w-contain w-full">
            <div class="bg-black rounded-lg px-xs text-white text-sm truncate overflow-hidden">
              {{ meta.data.title }}
            </div>
          </div>
        </div>
        <div class="text-gray-500 font-light text-sm mt-1">{{ getHostname() }}</div>
      </div>
    </template>
  </div>
</template>
