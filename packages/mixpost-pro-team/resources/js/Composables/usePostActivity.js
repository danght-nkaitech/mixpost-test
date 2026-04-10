import { computed, inject } from 'vue'

const usePostActivity = ({ context }) => {
  const routePrefix = inject('routePrefix')

  const VIEW_DEFAULT = 'default'
  const VIEW_THREAD = 'thread'

  const currentView = computed(() => {
    return context.activity.thread.parent_comment === null ? VIEW_DEFAULT : VIEW_THREAD
  })

  const isThreadView = computed(() => {
    return currentView.value === VIEW_THREAD
  })

  const threadParentComment = computed(() => {
    return context.activity.thread.parent_comment
  })

  const setThreadParentComment = comment => {
    context.activity.thread.parent_comment = comment
  }

  const getItem = ({ view, id }) => {
    return context.activity[view].items.find(item => item.id === id)
  }

  const addItem = ({ view, activity }) => {
    context.activity[view].items.push(activity)
  }

  const removeItem = ({ view, id }) => {
    const index = context.activity[view].items.findIndex(item => item.id === id)

    if (index > -1) {
      context.activity[view].items.splice(index, 1)
    }
  }

  const setItems = ({ view, items }) => {
    context.activity[view].items = items
  }

  const getItems = ({ view }) => {
    return context.activity[view].items
  }

  const getItemsCount = ({ view }) => {
    return context.activity[view].items.length
  }

  const getInitialized = ({ view }) => {
    return context.activity[view].isInitialized
  }

  const setInitialized = ({ view, state }) => {
    context.activity[view].isInitialized = state
  }

  const getLastScrollPosition = ({ view }) => {
    return context.activity[view].scrollPosition
  }

  const setLastScrollPosition = ({ view, state }) => {
    context.activity[view].scrollPosition = state
  }

  const getThreadParentItem = () => {
    return context.activity.thread.parent_comment
  }

  const getDraftCommentText = ({ view }) => {
    return context.activity[view].text
  }

  const setNewCommentText = ({ view, text }) => {
    context.activity[view].text = text
  }

  const destroyThread = () => {
    context.activity.thread.items = []
    context.activity.thread.parent_comment = null
    context.activity.thread.scrollPosition = 0
    context.activity.thread.text = ''
  }

  const fetchDefaultItems = params => {
    return new Promise((resolve, reject) => {
      axios
        .get(route(`${routePrefix}.posts.activities.index`, params))
        .then(function (response) {
          setItems({ view: VIEW_DEFAULT, items: response.data.data })
          setInitialized({ view: VIEW_DEFAULT, state: true })
          resolve(response)
        })
        .catch(reject)
    })
  }

  const fetchThreadItems = params => {
    return new Promise((resolve, reject) => {
      axios
        .get(route(`${routePrefix}.posts.comments.children`, params))
        .then(function (response) {
          setItems({ view: VIEW_THREAD, items: response.data.data })
          setInitialized({ view: VIEW_THREAD, state: true })
          resolve(response)
        })
        .catch(error => reject(error))
    })
  }

  return {
    VIEW_DEFAULT,
    VIEW_THREAD,
    currentView,
    isThreadView,
    threadParentComment,
    setThreadParentComment,
    getItem,
    addItem,
    removeItem,
    setItems,
    getItems,
    getItemsCount,
    getInitialized,
    setInitialized,
    getLastScrollPosition,
    setLastScrollPosition,
    getThreadParentItem,
    getDraftCommentText,
    setNewCommentText,
    destroyThread,
    fetchDefaultItems,
    fetchThreadItems
  }
}

export default usePostActivity
