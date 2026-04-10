<script setup>
import { inject, onMounted, onUnmounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, Link, router } from '@inertiajs/vue3'
import emitter from '@/Services/emitter'
import { cloneDeep, pickBy, throttle } from 'lodash'
import useRouter from '../../../Composables/useRouter'
import useSelectable from '@/Composables/useSelectable'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import Checkbox from '@/Components/Form/Checkbox.vue'
import Table from '@/Components/DataDisplay/Table.vue'
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import PureDangerButton from '@/Components/Button/PureDangerButton.vue'
import SelectableBar from '@/Components/DataDisplay/SelectableBar.vue'
import Pagination from '@/Components/Navigation/Pagination.vue'
import NoResult from '@/Components/Util/NoResult.vue'
import TrashIcon from '@/Icons/Trash.vue'
import WebhookItem from '../../../Components/Webhook/WebhookItem.vue'
import Plus from '../../../Icons/Plus.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'

const { t: $t } = useI18n()

const props = defineProps({
  filter: {
    type: Object,
    default: () => ({})
  },
  records: {
    type: Object
  }
})

const routePrefix = inject('routePrefix')
const workspaceCtx = inject('workspaceCtx')
const confirmation = inject('confirmation')

const {
  selectedRecords,
  putPageRecords,
  toggleSelectRecordsOnPage,
  deselectRecord,
  deselectAllRecords
} = useSelectable()

const { onError } = useRouter()

const filter = ref({
  keyword: props.filter.keyword
})

const itemsId = () => {
  return props.records.data.map(item => item.id)
}

const confirmDeleteWebhooks = () => {
  confirmation()
    .title($t('webhook.delete_webhooks'))
    .description($t('webhook.delete_webhooks_confirm'))
    .destructive()
    .onConfirm(dialog => {
      deleteWebhooksAfterConfirmed(dialog)
    })
    .show()
}

const deleteWebhooksAfterConfirmed = dialog => {
  dialog.isLoading(true)

  router.delete(route(`${routePrefix}.webhooks.deleteMultiple`, { workspace: workspaceCtx.id }), {
    data: {
      webhooks: selectedRecords.value
    },
    preserveScroll: true,
    onSuccess() {
      dialog.reset()
      deselectAllRecords()
    },
    onError(errors) {
      onError(errors, () => {
        deleteWebhooksAfterConfirmed(dialog)
      })
    },
    onFinish() {
      dialog.isLoading(false)
    }
  })
}

onMounted(() => {
  putPageRecords(itemsId())

  emitter.on('webhookDeleted', id => {
    deselectRecord(id)
  })
})

onUnmounted(() => {
  emitter.off('webhookDeleted')
})

watch(
  () => cloneDeep(filter.value),
  throttle(() => {
    router.get(
      route('mixpost.webhooks.index', { workspace: workspaceCtx.id }),
      pickBy(filter.value),
      {
        preserveState: true,
        only: ['records', 'filter']
      }
    )
  }, 300)
)

watch(
  () => props.records.data,
  () => {
    putPageRecords(itemsId())
  }
)
</script>
<template>
  <Head :title="$t('webhook.webhooks')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('webhook.webhooks')">
      <template #description>
        {{ $t('webhook.webhooks_desc') }}
      </template>
    </PageHeader>

    <div class="w-full row-px mt-lg">
      <Link :href="route(`${routePrefix}.webhooks.create`, { workspace: workspaceCtx.id })">
        <PrimaryButton size="sm">
          <template #icon>
            <Plus />
          </template>
          {{ $t('webhook.create_webhook') }}
        </PrimaryButton>
      </Link>

      <SelectableBar :count="selectedRecords.length" @close="deselectAllRecords">
        <PureDangerButton v-tooltip="$t('general.delete')" @click="confirmDeleteWebhooks">
          <TrashIcon />
        </PureDangerButton>
      </SelectableBar>

      <Panel :with-padding="false" class="mt-lg">
        <Table>
          <template #head>
            <TableRow>
              <TableCell component="th" scope="col" class="w-10">
                <Checkbox
                  v-model:checked="toggleSelectRecordsOnPage"
                  :disabled="!records.meta.total"
                />
              </TableCell>
              <TableCell component="th" scope="col" class="w-10"></TableCell>
              <TableCell component="th" scope="col">{{ $t('general.name') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('webhook.callback_url') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('post.status') }}</TableCell>
              <TableCell component="th" scope="col" />
            </TableRow>
          </template>
          <template #body>
            <template v-for="item in records.data" :key="item.id">
              <WebhookItem
                :item="item"
                @on-delete="
                  () => {
                    deselectRecord(item.id)
                  }
                "
              >
                <template #checkbox>
                  <Checkbox v-model:checked="selectedRecords" :value="item.id" />
                </template>
              </WebhookItem>
            </template>
          </template>
        </Table>

        <NoResult v-if="!records.meta.total" class="py-md px-md" />
      </Panel>

      <div v-if="records.meta.links.length > 3" class="mt-lg">
        <Pagination :meta="records.meta" :links="records.links" />
      </div>
    </div>
  </div>
</template>
