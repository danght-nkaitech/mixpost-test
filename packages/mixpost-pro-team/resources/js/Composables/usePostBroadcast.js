import { onMounted, onUnmounted, shallowRef } from 'vue'
import emitter from '@/Services/emitter'
import useSettings from './useSettings'
import useBroadcast from './useBroadcast'
import { parseDateTime } from '../helpers'
import useDateLocalize from './useDateLocalize'
import usePostActivity from './usePostActivity'

const usePostBroadcast = ({ props, context }) => {
  const { timeZone, timeFormat } = useSettings()
  const { connectBroadcastPrivate, leaveBroadcastChannel } = useBroadcast()
  const { translatedFormat } = useDateLocalize()
  const {
    VIEW_DEFAULT: ACTIVITY_VIEW_DEFAULT,
    VIEW_THREAD: ACTIVITY_VIEW_THREAD,
    currentView: currentActivityView,
    threadParentComment: activityThreadParentComment,
    getItem: getActivityItem,
    addItem: addActivityItem,
    removeItem: removeActivityItem,
    destroyThread: destroyActivityThread
  } = usePostActivity({ context })

  const broadcastConnection = shallowRef(null)

  const dateTimeFormat = datetime => {
    const { zonedDateTime, format } = parseDateTime(datetime, timeZone, timeFormat)

    return translatedFormat(zonedDateTime, format)
  }

  const listenToPostActivity = () => {
    const getActivityView = activity => {
      return !activity.is_child ? ACTIVITY_VIEW_DEFAULT : ACTIVITY_VIEW_THREAD
    }

    if (!broadcastConnection.value) return

    broadcastConnection.value.listen('Post.PostActivityCreated', activity => {
      const activityView = getActivityView(activity)

      if (!context.activity[activityView].isInitialized) return
      if (currentActivityView.value === ACTIVITY_VIEW_DEFAULT && activity.is_child) return

      // Localize timestamps
      activity.timestamps.localized.created_at = dateTimeFormat(
        activity.timestamps['Iso8601'].created_at
      )

      // Localize all date_times keys
      Object.keys(activity.date_times).forEach(key => {
        const value = activity.date_times[key]
        activity.date_times[key].localized = dateTimeFormat(value.Iso8601)
      })

      const item = getActivityItem({ view: activityView, id: activity.id })
      if (item) return

      addActivityItem({ view: activityView, activity })

      emitter.emit('postActivityCreated')
    })

    broadcastConnection.value.listen('Post.PostCommentUpdated', activity => {
      const activityView = getActivityView(activity)
      if (!context.activity[activityView].isInitialized) return

      const { id, text, children_count } = activity

      const item = getActivityItem({ view: activityView, id })
      if (item) item.text = text
      item.children_count = children_count
    })

    broadcastConnection.value.listen('Post.PostCommentReactionsUpdated', activity => {
      const activityView = getActivityView(activity)
      if (!context.activity[activityView].isInitialized) return

      const { id, reactions } = activity

      const item = getActivityItem({ view: activityView, id })
      if (item) item.reactions = reactions
    })

    broadcastConnection.value.listen('Post.PostCommentDeleted', data => {
      const { id } = data

      removeActivityItem({ view: ACTIVITY_VIEW_DEFAULT, id })
      removeActivityItem({ view: ACTIVITY_VIEW_THREAD, id })

      if (activityThreadParentComment.value && activityThreadParentComment.value.id === id) {
        destroyActivityThread()
      }
    })
  }

  const connectToBroadcast = () => {
    if (!props.post) {
      return
    }

    if (broadcastConnection.value) {
      return
    }

    broadcastConnection.value = connectBroadcastPrivate(`mixpost_posts.${props.post.id}`)

    listenToPostActivity()
  }

  onMounted(() => {
    connectToBroadcast()
  })

  onUnmounted(() => {
    if (props.post) {
      leaveBroadcastChannel(`mixpost_posts.${props.post.id}`)
    }
  })

  return {
    connectToBroadcast
  }
}

export default usePostBroadcast
