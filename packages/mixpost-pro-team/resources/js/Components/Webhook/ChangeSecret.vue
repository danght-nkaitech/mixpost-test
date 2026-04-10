<script setup>
import { inject, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Label from '../Form/Label.vue'
import Input from '../Form/Input.vue'
import Error from '../Form/Error.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import Alert from '../Util/Alert.vue'
import DialogModal from '../Modal/DialogModal.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import useRouter from '../../Composables/useRouter'

const props = defineProps({
  webhookId: {
    required: true,
    type: String
  }
})

const routePrefix = inject('routePrefix')
const workspaceCtx = inject('workspaceCtx')
const authPasswordConfirmation = inject('authPasswordConfirmation')
const modal = ref(false)
const form = useForm({
  secret: ''
})

const { onError } = useRouter()

const open = () => {
  authPasswordConfirmation()
    .onConfirm(() => {
      modal.value = true
    })
    .ensureConfirmed()
}

const close = () => {
  modal.value = false
  form.reset()
}

const submit = () => {
  const url = workspaceCtx
    ? route(`${routePrefix}.webhooks.updateSecret`, {
        workspace: workspaceCtx.id,
        webhook: props.webhookId
      })
    : route(`${routePrefix}.system.webhooks.updateSecret`, {
        webhook: props.webhookId
      })
  form.post(url, {
    preserveScroll: true,
    onSuccess() {
      close()
    },
    onError(errors) {
      onError(errors, submit)
    }
  })
}
</script>
<template>
  <Alert variant="warning" :closeable="false" class="mb-lg">
    {{ $t('webhook.change_secret_desc') }}
  </Alert>

  <SecondaryButton
    :is-loading="authPasswordConfirmation().data.value.ensuringPasswordConfirmed"
    :disabled="authPasswordConfirmation().data.value.ensuringPasswordConfirmed"
    @click="open"
  >
    {{ $t('webhook.change_secret') }}
  </SecondaryButton>

  <DialogModal
    :show="modal"
    max-width="sm"
    :scrollable-body="true"
    :closeable="true"
    @close="close"
  >
    <template #header>
      {{ $t('webhook.change_secret') }}
    </template>

    <template #body>
      <VerticalGroup>
        <template #title>
          <Label for="secret">{{ $t('webhook.secret') }}</Label>
        </template>

        <Input id="secret" v-model="form.secret" type="text" :placeholder="$t('webhook.secret')" />

        <template #footer>
          <Error :message="form.errors.secret" />
        </template>
      </VerticalGroup>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="close">{{ $t('general.cancel') }}</SecondaryButton>
      <PrimaryButton :is-loading="form.processing" :disabled="form.processing" @click="submit">
        {{ $t('general.save') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
