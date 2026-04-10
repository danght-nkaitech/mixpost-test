import { onMounted, onUnmounted, ref } from 'vue'

const useOffline = () => {
  const offline = ref(false)

  const setOffline = () => {
    offline.value = true
  }

  const setOnline = () => {
    offline.value = false
  }

  onMounted(() => {
    window.addEventListener('offline', setOffline)
    window.addEventListener('online', setOnline)
  })

  onUnmounted(() => {
    window.removeEventListener('offline', setOffline)
    window.removeEventListener('online', setOnline)
  })

  return {
    offline,
    setOffline,
    setOnline
  }
}

export default useOffline
