import NProgress from 'nprogress'
import { computed, inject, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { debounce } from 'lodash'
import useNotifications from '@/Composables/useNotifications'
import { usePage } from '@inertiajs/vue3'

const useMedia = (
  routeName = 'mixpost.media.fetchUploads',
  routeParams = {},
  maxSelectedItems = -1,
  mimeTypes = [],
  selectedItems = []
) => {
  const { t: $t } = useI18n()
  const { notify } = useNotifications()
  const routePrefix = inject('routePrefix')

  const activeTab = ref('uploads')

  const tabs = computed(() => {
    const sources = ['uploads', 'stock']
    if (!mimeTypes.length || (mimeTypes.length && mimeTypes.includes('image/gif'))) {
      sources.push('gifs')
    }
    if (usePage().props.is_configured_service.adobe_express) {
      sources.push('new_design')
    }
    return sources
  })

  const isLoaded = ref(false)
  const isFetching = ref(false)
  const isDownloading = ref(false)
  const isDeleting = ref(false)
  const page = ref(1)
  const items = ref([])
  const endlessPagination = ref(null)
  const keyword = ref('')

  const selected = ref(selectedItems)
  const toggleSelect = media => {
    const index = selected.value.findIndex(item => item.id === media.id)

    if (index < 0 && !Object.prototype.hasOwnProperty.call(media, 'error')) {
      if (maxSelectedItems === 1) {
        selected.value = [media]
      } else if (selected.value.length < maxSelectedItems || maxSelectedItems === -1) {
        // -1 means infinite
        selected.value.push(media)
      }
    }

    if (index >= 0) {
      selected.value.splice(index, 1)
    }
  }

  const deselectAll = () => {
    selected.value = []
  }

  const isSelected = media => {
    const index = selected.value.findIndex(item => item.id === media.id)

    return index !== -1
  }

  const PREFETCH_MARGIN = 500

  const isSentinelVisible = () => {
    if (!endlessPagination.value) return false
    const rect = endlessPagination.value.getBoundingClientRect()
    return rect.top < window.innerHeight && rect.bottom >= 0
  }

  const fetchItems = (appendResult = true) => {
    if (!page.value || isFetching.value) {
      return
    }

    isFetching.value = true
    NProgress.start()

    const params = {
      page: page.value,
      keyword: keyword.value,
      mime_types: mimeTypes
    }

    axios
      .get(route(routeName, routeParams), { params })
      .then(function (response) {
        const nextLink = response.data.links.next

        if (nextLink) {
          page.value = response.data.links.next.split('?page=')[1]
        }

        if (!nextLink) {
          page.value = 0
        }

        if (!appendResult) {
          items.value = response.data.data
        }

        if (appendResult) {
          items.value = [...items.value, ...response.data.data]
        }
      })
      .catch(() => {
        notify('error', $t('media.error_retrieving_media'))
      })
      .finally(() => {
        NProgress.done()
        isFetching.value = false
        isLoaded.value = true

        nextTick(() => {
          if (page.value && isSentinelVisible()) {
            fetchItems()
          }
        })
      })
  }

  const downloadExternal = (items, callback) => {
    isDownloading.value = true
    NProgress.start()

    axios
      .post(route(`${routePrefix}.media.download`, routeParams), {
        items,
        from: activeTab.value
      })
      .then(response => {
        callback(response)
      })
      .catch(() => {
        notify('error', $t('media.error_downloading_media'))
      })
      .finally(() => {
        isDownloading.value = false
        NProgress.done()
        NProgress.remove()
      })
  }

  const updateItem = updatedMedia => {
    const index = items.value.findIndex(item => item.id === updatedMedia.id)
    if (index !== -1) {
      items.value[index] = { ...items.value[index], ...updatedMedia }
    }
  }

  const removeItems = ids => {
    items.value = items.value.filter(item => !ids.includes(item.id))
  }

  const getMediaCrediting = mediaCollection => {
    let text = ''

    mediaCollection.forEach(item => {
      if (item.source === 'Unsplash') {
        text += `\nPhoto by ${item.author} on Unsplash`
      }
      if (item.source === 'Pexels') {
        text += `\nPhoto by ${item.author} on Pexels`
      }
      if (item.source === 'Tenor') {
        text += `\nGIF by ${item.author} on Tenor`
      }
      if (item.source === 'Giphy') {
        text += `\nGIF by ${item.author} on Giphy`
      }
    })

    return text
  }

  const deletePermanently = (items, callback) => {
    isDeleting.value = true
    NProgress.start()

    axios
      .delete(route(`${routePrefix}.media.delete`, routeParams), {
        data: {
          items
        }
      })
      .then(() => {
        callback()
      })
      .catch(() => {
        notify('error', $t('media.error_deleting_media'))
      })
      .finally(() => {
        isDeleting.value = false
        NProgress.done()
        NProgress.remove()
      })
  }

  let observer = null

  const createObserver = () => {
    observer = new IntersectionObserver(
      entries => {
        if (entries[0].isIntersecting && page.value && !isFetching.value) {
          fetchItems()
        }
      },
      { rootMargin: `0px 0px ${PREFETCH_MARGIN}px 0px` }
    )

    nextTick(() => {
      if (endlessPagination.value) {
        observer.observe(endlessPagination.value)
      }
    })
  }

  const debouncedFetch = debounce(() => {
    page.value = 1
    fetchItems(false)
  }, 300)

  onBeforeUnmount(() => {
    debouncedFetch.cancel()

    if (observer) {
      observer.disconnect()
      observer = null
    }
  })

  watch(keyword, debouncedFetch)

  return {
    activeTab,
    tabs,
    isLoaded,
    isDownloading,
    isDeleting,
    keyword,
    page,
    items,
    endlessPagination,
    selected,
    getMediaCrediting,
    downloadExternal,
    deletePermanently,
    updateItem,
    removeItems,
    createObserver,
    toggleSelect,
    deselectAll,
    isSelected
  }
}

export default useMedia
