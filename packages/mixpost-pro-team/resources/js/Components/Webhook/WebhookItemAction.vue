<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import emitter from '@/Services/emitter'
import { router } from '@inertiajs/vue3'
import useRouter from '../../Composables/useRouter'
import PureButtonLink from '@/Components/Button/PureButtonLink.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import Trash from '../../Icons/Trash.vue'
import DropdownItem from '../Dropdown/DropdownItem.vue'
import DropdownButton from '../Dropdown/DropdownButton.vue'
import QueueList from '../../Icons/QueueList.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')
const workspaceCtx = inject('workspaceCtx')

const props = defineProps({
  itemId: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['onDelete'])

const { onError } = useRouter()

const getRoute = name => {
  switch (name) {
    case 'edit':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.edit`, {
            workspace: workspaceCtx.id,
            webhook: props.itemId
          })
        : route(`${routePrefix}.system.webhooks.edit`, {
            webhook: props.itemId
          })
    case 'deliveries':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.deliveries.index`, {
            workspace: workspaceCtx.id,
            webhook: props.itemId
          })
        : route(`${routePrefix}.system.webhooks.deliveries.index`, {
            webhook: props.itemId
          })
    case 'delete':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.delete`, {
            workspace: workspaceCtx.id,
            webhook: props.itemId
          })
        : route(`${routePrefix}.system.webhooks.delete`, {
            webhook: props.itemId
          })
    default:
      return ''
  }
}

const confirmationDeletion = ref(false)

const confirmDeleteWebhook = () => {
  confirmation()
    .title($t('webhook.delete_webhook'))
    .description($t('webhook.delete_webhook_confirm'))
    .destructive()
    .onConfirm(dialog => {
      deleteWebhookAfterConfirmed(dialog)
    })
    .show()
}
const deleteWebhookAfterConfirmed = dialog => {
  dialog.isLoading(true)

  router.delete(getRoute('delete'), {
    onSuccess() {
      confirmationDeletion.value = false
      emit('onDelete')
      emitter.emit('webhookDeleted', props.itemId)
      dialog.reset()
    },
    onError(errors) {
      onError(errors, () => {
        deleteWebhookAfterConfirmed(dialog)
      })
    },
    onFinish() {
      dialog.isLoading(false)
    }
  })
}
</script>
<template>
  <div>
    <div class="flex flex-row items-center justify-end gap-xs">
      <PureButtonLink v-tooltip="$t('webhook.deliveries')" :href="getRoute('deliveries')">
        <QueueList />
      </PureButtonLink>

      <Dropdown placement="bottom-end">
        <template #trigger>
          <DropdownButton />
        </template>

        <template #content>
          <DropdownItem :href="getRoute('edit')">
            <template #icon>
              <PencilSquare />
            </template>
            {{ $t('general.edit') }}
          </DropdownItem>

          <DropdownItem as="button" @click="confirmDeleteWebhook">
            <template #icon>
              <Trash class="text-red-500" />
            </template>
            {{ $t('general.delete') }}
          </DropdownItem>
        </template>
      </Dropdown>
    </div>
  </div>
</template>
