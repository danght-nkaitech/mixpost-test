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

    <template v-else>
      <div class="rounded-lg border">
        <template v-if="meta.data.default.image">
          <img :src="meta.data.default.image" alt="Bluesky URL Preview" class="rounded-t-lg" />
        </template>
        <div class="px-sm py-xs border-t">
          <div class="text-black">
            {{ meta.data.default.title ? meta.data.default.title : getHostname() }}
          </div>
          <template v-if="meta.data.default.description">
            <div class="text-sm text-gray-600">{{ meta.data.default.description }}</div>
          </template>
          <div class="text-xs text-gray-400 border-t pt-xs mt-xs">{{ getHostname() }}</div>
        </div>
      </div>
    </template>
  </div>
</template>
