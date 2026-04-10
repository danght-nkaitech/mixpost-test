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
  return getURLMeta(props.url)
})
</script>

<template>
  <div v-if="meta">
    <template v-if="meta.isLoading">
      <PreloaderSkeleton />
    </template>

    <template v-if="!meta.isLoading && meta.data.default.image">
      <div class="border">
        <img :src="meta.data.default.image" alt="LinkedIn URL Preview" />
        <div class="p-sm bg-[#F0F2F5]">
          <div class="text-black font-medium">{{ meta.data.default.title }}</div>
          <div class="text-gray-500 font-light">{{ getHostname() }}</div>
        </div>
      </div>
    </template>
  </div>
</template>
