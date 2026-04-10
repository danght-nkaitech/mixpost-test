import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const useConfig = () => {
  const config = computed(() => {
    return usePage().props.mixpost.config
  })

  const getConfig = name => {
    return config.value[name]
  }

  return {
    getConfig,
    mimeTypes: config.value.mime_types,
    chunkedUpload: config.value.chunked_upload
  }
}

export default useConfig
