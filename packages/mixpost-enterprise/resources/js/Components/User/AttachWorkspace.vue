<script setup>
import { inject, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DialogModal from '../Modal/DialogModal.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import UserRole from '../Workspace/UserRole.vue'
import Plus from '../../Icons/Plus.vue'
import SelectWorkspace from '../Workspace/SelectWorkspace.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import CanApprove from '../Workspace/CanApprove.vue'
import IsOwner from '../Workspace/IsOwner.vue'

const routePrefix = inject('routePrefix')

const props = defineProps({
  user: {
    type: Object
  },
  attachedWorkspaces: {
    type: Array,
    default: () => []
  }
})

const selectedWorkspace = ref(null)

const formAttach = useForm({
  user_id: props.user.id,
  role: 'admin',
  can_approve: false,
  is_owner: false
})

const attach = () => {
  formAttach.post(
    route(`${routePrefix}.workspaces.users.store`, { workspace: selectedWorkspace.value.key }),
    {
      preserveScroll: true,
      onSuccess() {
        close()
        formAttach.reset()
      }
    }
  )
}

const modal = ref(false)

const open = () => {
  modal.value = true
}

const close = () => {
  modal.value = false
  selectedWorkspace.value = null
}
</script>
<template>
  <SecondaryButton size="sm" @click="open">
    <template #icon>
      <Plus />
    </template>

    {{ $t('general.attach') }}
  </SecondaryButton>

  <DialogModal
    :show="modal"
    max-width="md"
    :scrollable-body="true"
    :closeable="true"
    @close="close"
  >
    <template #header>
      {{ $t('workspace.attach_workspace') }}
    </template>

    <template #body>
      <SelectWorkspace v-model="selectedWorkspace" :exclude="attachedWorkspaces" />
      <UserRole v-model="formAttach.role" class="mt-lg" />

      <VerticalGroup class="mt-lg">
        <CanApprove v-model="formAttach.can_approve" />
      </VerticalGroup>

      <VerticalGroup class="mt-lg">
        <IsOwner v-model="formAttach.is_owner" />
      </VerticalGroup>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs rtl:mr-0 rtl:ml-xs" @click="close">{{
        $t('general.cancel')
      }}</SecondaryButton>
      <PrimaryButton
        :disabled="!selectedWorkspace || formAttach.processing"
        :is-loading="formAttach.processing"
        @click="attach"
      >
        {{ $t('general.attach') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
