import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const useEnabledFeatures = () => {
  const features = computed(() => {
    return usePage().props.mixpost.features
  })

  return {
    features
  }
}

export default useEnabledFeatures
