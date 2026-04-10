<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useForm } from '@inertiajs/vue3'
import useNotifications from '../../Composables/useNotifications'
import DialogModal from '../Modal/DialogModal.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import UserRole from '../Workspace/UserRole.vue'
import Plus from '../../Icons/Plus.vue'
import SelectUser from '../User/SelectUser/SelectUser.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import CanApprove from './CanApprove.vue'
import IsOwner from './IsOwner.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const { notify } = useNotifications()

const props = defineProps({
  workspace: {
    type: Object
  },
  attachedUsers: {
    type: Array,
    default: () => []
  }
})

const selectedUser = ref(null)

const formAttach = useForm({
  user_id: null,
  role: 'admin',
  can_approve: false,
  is_owner: false
})

const attach = () => {
  formAttach
    .transform(data => {
      return {
        ...data,
        user_id: selectedUser.value.key
      }
    })
    .post(route(`${routePrefix}.workspaces.users.store`, { workspace: props.workspace.uuid }), {
      preserveScroll: true,
      onSuccess() {
        close()
        formAttach.reset()

        notify('success', $t('user.user_attached'))
      }
    })
}

const modal = ref(false)

const open = () => {
  modal.value = true
}

const close = () => {
  modal.value = false
  selectedUser.value = null
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
      {{ $t('user.attach_user') }}
    </template>

    <template #body>
      <SelectUser v-model="selectedUser" :exclude="attachedUsers" />

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
        :disabled="!selectedUser || formAttach.processing"
        :is-loading="formAttach.processing"
        @click="attach"
        >{{ $t('general.attach') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
