import { inject, ref } from 'vue'

const usePreloader = ({ delay = 250, useGlobal = false }) => {
  const appContext = inject('appCtx')
  const isLoadingPreloader = ref(false)
  let timeoutId = null

  const startPreloader = () => {
    timeoutId = setTimeout(() => {
      isLoadingPreloader.value = true

      if (useGlobal) {
        appContext.preloader = true
      }
    }, delay)
  }

  const stopPreloader = () => {
    if (timeoutId) {
      clearTimeout(timeoutId)
    }

    isLoadingPreloader.value = false

    if (useGlobal) {
      appContext.preloader = false
    }
  }

  return {
    isLoadingPreloader,
    startPreloader,
    stopPreloader
  }
}

export default usePreloader
