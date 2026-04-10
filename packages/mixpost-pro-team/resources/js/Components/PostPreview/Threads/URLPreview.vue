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
          <img :src="meta.data.default.image" alt="Facebook URL Preview" class="rounded-t-lg" />
        </template>
        <div class="p-sm border-t">
          <div class="text-xs text-gray-400">{{ getHostname() }}</div>
          <div class="text-black text-sm">
            {{ meta.data.default.title ? meta.data.default.title : getHostname() }}
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
