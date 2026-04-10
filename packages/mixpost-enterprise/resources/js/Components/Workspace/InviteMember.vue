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
import Input from '../Form/Input.vue'
import Error from '../Form/Error.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import CanApprove from './CanApprove.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const { notify } = useNotifications()

const props = defineProps({
  workspace: {
    type: Object
  }
})

const form = useForm({
  email: '',
  role: 'admin',
  can_approve: false
})

const sendInvite = () => {
  form.post(
    route(`${routePrefix}.workspace.invitations.invite`, { workspace: props.workspace.uuid }),
    {
      preserveScroll: true,
      onSuccess() {
        close()
        form.reset()

        notify('success', $t('team.member_invited'))
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
}
</script>
<template>
  <SecondaryButton size="sm" @click="open">
    <Plus class="mr-xs" />
    {{ $t('team.invite_member') }}
  </SecondaryButton>

  <DialogModal
    :show="modal"
    max-width="md"
    :scrollable-body="true"
    :closeable="true"
    @close="close"
  >
    <template #header>
      {{ $t('team.invite_member') }}
    </template>

    <template #body>
      <VerticalGroup>
        <template #title>{{ $t('general.email') }}</template>
        <Input
          v-model="form.email"
          type="email"
          :error="form.errors.email !== undefined"
          :placeholder="$t('onboarding.email_address')"
        />
        <template #footer>
          <Error :message="form.errors.email" />
          <Error :message="form.errors.limit" />
        </template>
      </VerticalGroup>
      <UserRole v-model="form.role" class="mt-lg" />
      <VerticalGroup class="mt-lg">
        <CanApprove v-model="form.can_approve" />
      </VerticalGroup>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="close">{{ $t('general.cancel') }}</SecondaryButton>
      <PrimaryButton :disabled="form.processing" :is-loading="form.processing" @click="sendInvite">
        {{ $t('team.invite') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
