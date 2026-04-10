<script setup>
import { inject, onMounted, onUnmounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, router, Link } from '@inertiajs/vue3'
import { cloneDeep, pickBy, throttle } from 'lodash'
import emitter from '@/Services/emitter'
import useNotifications from '@/Composables/useNotifications'
import useSelectable from '@/Composables/useSelectable'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import PureDangerButton from '@/Components/Button/PureDangerButton.vue'
import Panel from '@/Components/Surface/Panel.vue'
import Checkbox from '@/Components/Form/Checkbox.vue'
import Table from '@/Components/DataDisplay/Table.vue'
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import SelectableBar from '@/Components/DataDisplay/SelectableBar.vue'
import NoResult from '@/Components/Util/NoResult.vue'
import TrashIcon from '@/Icons/Trash.vue'
import Pagination from '@/Components/Navigation/Pagination.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import Filters from '../../../Components/Receipt/Filters.vue'
import ReceiptItem from '../../../Components/Receipt/ReceiptItem.vue'
import Flex from '../../../Components/Layout/Flex.vue'

const props = defineProps({
  receipts: {
    type: Object
  },
  filter: {
    type: Object,
    default: () => ({})
  }
})

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const { notify } = useNotifications()
const confirmation = inject('confirmation')

const {
  selectedRecords,
  putPageRecords,
  toggleSelectRecordsOnPage,
  deselectRecord,
  deselectAllRecords
} = useSelectable()

const itemsId = () => {
  return props.receipts.data.map(item => item.uuid)
}

const filter = ref({
  invoice_number: props.filter.invoice_number
})

onMounted(() => {
  putPageRecords(itemsId())

  emitter.on('receiptDelete', id => {
    deselectRecord(id)
  })
})

onUnmounted(() => {
  emitter.off('receiptDelete')
})

watch(
  () => props.receipts.data,
  () => {
    putPageRecords(itemsId())
  }
)

watch(
  () => cloneDeep(filter.value),
  throttle(() => {
    router.get(route(`${routePrefix}.receipts.index`), pickBy(filter.value), {
      preserveState: true,
      only: ['receipts', 'filter']
    })
  }, 300)
)

const deleteReceipts = () => {
  confirmation()
    .title($t('finance.delete_receipts'))
    .description($t('finance.confirm_delete_receipts'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(route(`${routePrefix}.receipts.multipleDelete`), {
        data: {
          receipts: selectedRecords.value
        },
        preserveScroll: true,
        onSuccess() {
          deselectAllRecords()
          notify('success', $t('finance.selected_receipts_deleted'))
        },
        onFinish() {
          dialog.reset()
        }
      })
    })
    .show()
}
</script>
<template>
  <Head :title="$t('sidebar.receipts')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('sidebar.receipts')">
      <template #description>{{ $t('finance.manage_receipts') }}</template>
    </PageHeader>

    <div class="mt-lg row-px w-full">
      <SelectableBar :count="selectedRecords.length" @close="deselectAllRecords">
        <PureDangerButton v-tooltip="$t('general.delete')" @click="deleteReceipts">
          <TrashIcon />
        </PureDangerButton>
      </SelectableBar>

      <Flex class="justify-between">
        <Link :href="route(`${routePrefix}.receipts.create`)" class="mb-xs sm:mb-0">
          <PrimaryButton>{{ $t('finance.create_receipt') }}</PrimaryButton>
        </Link>

        <Filters v-model="filter" />
      </Flex>

      <Panel :with-padding="false" class="mt-lg">
        <Table>
          <template #head>
            <TableRow>
              <TableCell component="th" scope="col" class="w-10">
                <Checkbox
                  v-model:checked="toggleSelectRecordsOnPage"
                  :disabled="!receipts.meta.total"
                />
              </TableCell>
              <TableCell component="th" scope="col">{{ $t('finance.invoice_number') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('workspace.workspace') }}</TableCell>
              <TableCell component="th" scope="col"> {{ $t('finance.amount') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('finance.tax') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('finance.currency') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('finance.billing_date') }}</TableCell>
              <TableCell component="th" scope="col" />
            </TableRow>
          </template>
          <template #body>
            <template v-for="item in $page.props.receipts.data" :key="item.uuid">
              <ReceiptItem
                :item="item"
                @on-delete="
                  () => {
                    deselectRecord(item.uuid)
                  }
                "
              >
                <template #checkbox>
                  <Checkbox v-model:checked="selectedRecords" :value="item.uuid" />
                </template>
              </ReceiptItem>
            </template>
          </template>
        </Table>

        <NoResult v-if="!receipts.meta.total" table />
      </Panel>

      <div v-if="receipts.meta.links.length > 3" class="mt-lg">
        <Pagination :meta="receipts.meta" :links="receipts.links" />
      </div>
    </div>
  </div>
</template>
