<script setup>
import { Head } from '@inertiajs/vue3'
import { inject } from 'vue'
import WorkspaceLayout from '@/Layouts/Workspace.vue'
import WorkspaceTeamMembers from '../../../Components/Workspace/WorkspaceMembers.vue'
import PageHeader from '../../../Components/DataDisplay/PageHeader.vue'

defineOptions({ layout: WorkspaceLayout })

inject('routePrefix')

defineProps({
  workspace: {
    required: true,
    type: Object
  },
  users: {
    required: true,
    type: Array
  },
  invitations: {
    required: true,
    type: Array
  }
})
</script>
<template>
  <Head :title="$t('team.team')" />

  <PageHeader :title="$t('team.team')" :with-padding-x="false" />

  <div class="w-full">
    <WorkspaceTeamMembers :workspace="workspace" :users="users" :invitations="invitations" />
  </div>
</template>
