import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import useWorkspace from './useWorkspace.js'

const usePost = () => {
  const { isWorkspaceEditorRole } = useWorkspace()

  const post = computed(() => {
    return usePage().props.post
  })

  const postId = computed(() => {
    return post.value ? post.value.id : null
  })

  const isInHistory = computed(() => {
    if (!post.value) {
      return false
    }

    return ['published', 'failed'].includes(post.value.status)
  })

  const isScheduleProcessing = computed(() => {
    if (!post.value) {
      return false
    }

    return post.value.status === 'publishing'
  })

  const needsApproval = computed(() => {
    if (!post.value) {
      return false
    }

    return post.value.status === 'needs_approval'
  })

  const editAllowed = computed(() => {
    return !(
      isInHistory.value ||
      isScheduleProcessing.value ||
      (post.value && post.value.trashed) ||
      !isWorkspaceEditorRole.value
    )
  })

  const userCanApprove = computed(() => {
    return usePage().props.user_can_approve
  })

  return {
    postId,
    isInHistory,
    isScheduleProcessing,
    needsApproval,
    editAllowed,
    userCanApprove
  }
}

export default usePost
