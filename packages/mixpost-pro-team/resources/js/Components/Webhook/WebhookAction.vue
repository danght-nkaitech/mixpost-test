<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link, router } from '@inertiajs/vue3'
import Trash from '../../Icons/Trash.vue'
import DangerButton from '../Button/DangerButton.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import Flex from '../Layout/Flex.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import Plus from '../../Icons/Plus.vue'
import QueueList from '../../Icons/QueueList.vue'
import useRouter from '../../Composables/useRouter'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')
const workspaceCtx = inject('workspaceCtx')

const props = defineProps({
  record: {
    type: Object
  },
  deliveries: {
    type: Boolean,
    default: true
  },
  create: {
    type: Boolean,
    default: true
  },
  edit: {
    type: Boolean,
    default: true
  },
  destroy: {
    type: Boolean,
    default: true
  }
})

const { onError } = useRouter()

const getRoute = name => {
  switch (name) {
    case 'create':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.create`, {
            workspace: workspaceCtx.id,
            webhook: props.record.id
          })
        : route(`${routePrefix}.system.webhooks.create`, {
            webhook: props.record.id
          })
    case 'edit':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.edit`, {
            workspace: workspaceCtx.id,
            webhook: props.record.id
          })
        : route(`${routePrefix}.system.webhooks.edit`, {
            webhook: props.record.id
          })
    case 'deliveries':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.deliveries.index`, {
            workspace: workspaceCtx.id,
            webhook: props.record.id
          })
        : route(`${routePrefix}.system.webhooks.deliveries.index`, {
            webhook: props.record.id
          })
    case 'delete':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.delete`, {
            workspace: workspaceCtx.id,
            webhook: props.record.id
          })
        : route(`${routePrefix}.system.webhooks.delete`, {
            webhook: props.record.id
          })
    default:
      return ''
  }
}

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
    preserveScroll: true,
    onError(errors) {
      onError(errors, () => {
        deleteWebhookAfterConfirmed(dialog)
      })
    },
    onFinish() {
      dialog.reset()
    }
  })
}
</script>
<template>
  <Flex :responsive="false" class="items-center">
    <template v-if="create">
      <Link :href="getRoute('create')">
        <PrimaryButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <Plus />
          </template>
          {{ $t('general.create') }}
        </PrimaryButton>
      </Link>
    </template>

    <template v-if="deliveries">
      <Link :href="getRoute('deliveries')">
        <SecondaryButton size="sm">
          <template #icon>
            <QueueList />
          </template>
          {{ $t('webhook.deliveries') }}
        </SecondaryButton>
      </Link>
    </template>

    <template v-if="edit">
      <Link :href="getRoute('edit')">
        <PrimaryButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <PencilSquare />
          </template>
          {{ $t('general.edit') }}
        </PrimaryButton>
      </Link>
    </template>

    <template v-if="destroy">
      <DangerButton size="sm" @click="confirmDeleteWebhook">
        <template #icon>
          <Trash />
        </template>
      </DangerButton>
    </template>
  </Flex>
</template>
