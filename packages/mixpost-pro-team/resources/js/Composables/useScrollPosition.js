import { ref, onMounted, onBeforeUnmount } from 'vue'

const useScrollPosition = elementRef => {
  const scrollPosition = ref(0)

  const handleScroll = () => {
    scrollPosition.value = elementRef.value.scrollTop
  }

  const setScrollPosition = position => {
    elementRef.value.scrollTop = position
  }

  const scrollToBottom = () => {
    setScrollPosition(elementRef.value.scrollHeight - elementRef.value.clientHeight)
  }

  const isNearBottom = () => {
    const { scrollTop, scrollHeight, clientHeight } = elementRef.value
    return scrollTop + clientHeight >= scrollHeight - 5
  }

  onMounted(() => {
    elementRef.value.addEventListener('scroll', handleScroll)
  })

  onBeforeUnmount(() => {
    elementRef.value.removeEventListener('scroll', handleScroll)
  })

  return {
    scrollPosition,
    setScrollPosition,
    scrollToBottom,
    isNearBottom
  }
}

export default useScrollPosition
