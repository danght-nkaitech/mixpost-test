import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import useAuth from './useAuth'

const useWorkspace = () => {
  const { user, workspaces } = useAuth()

  const activeWorkspaceId = computed(() => {
    return usePage().props.ziggy.workspace_id
  })

  const activeWorkspace = computed(() => {
    return workspaces.value.find(workspace => workspace.uuid === activeWorkspaceId.value)
  })

  const workspaceRole = computed(() => {
    if (!activeWorkspace.value) {
      return null
    }

    return activeWorkspace.value.pivot.role
  })

  const isWorkspaceAdminRole = computed(() => {
    return workspaceRole.value === 'admin'
  })

  const isWorkspaceMemberRole = computed(() => {
    return workspaceRole.value === 'member'
  })

  const isWorkspaceOwner = computed(() => {
    return user.value.id === activeWorkspace.value.owner_id
  })

  return {
    activeWorkspaceId,
    activeWorkspace,
    isWorkspaceOwner,
    workspaceRole,
    isWorkspaceAdminRole,
    isWorkspaceMemberRole
  }
}

export default useWorkspace
