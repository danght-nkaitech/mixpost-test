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
import WorkspaceItem from '../../../Components/Workspace/WorkspaceItem.vue'
import Filters from '../../../Components/Workspace/Filters.vue'
import Flex from '../../../Components/Layout/Flex.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const props = defineProps({
  workspaces: {
    type: Object
  },
  filter: {
    type: Object,
    default: () => ({})
  }
})

const confirmation = inject('confirmation')
const { notify } = useNotifications()

const {
  selectedRecords,
  putPageRecords,
  toggleSelectRecordsOnPage,
  deselectRecord,
  deselectAllRecords
} = useSelectable()

const itemsId = () => {
  return props.workspaces.data.map(item => item.uuid)
}

const filter = ref({
  keyword: props.filter.keyword,
  subscription_status: props.filter.subscription_status,
  free: props.filter.free,
  access_status: props.filter.access_status
})

onMounted(() => {
  putPageRecords(itemsId())

  emitter.on('workspaceDelete', id => {
    deselectRecord(id)
  })
})

onUnmounted(() => {
  emitter.off('workspaceDelete')
})

watch(
  () => props.workspaces.data,
  () => {
    putPageRecords(itemsId())
  }
)

watch(
  () => cloneDeep(filter.value),
  throttle(() => {
    router.get(route(`${routePrefix}.workspaces.index`), pickBy(filter.value), {
      preserveState: true,
      only: ['workspaces', 'filter']
    })
  }, 300)
)

const deleteWorkspaces = () => {
  confirmation()
    .title($t('workspace.delete_workspaces'))
    .description(
      `${$t('workspace.confirm_delete_workspaces')}<br><br>${$t('workspace.data_delete')}`
    )
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(route(`${routePrefix}.workspaces.multipleDelete`), {
        data: {
          workspaces: selectedRecords.value
        },
        preserveScroll: true,
        onSuccess() {
          deselectAllRecords()
          notify('success', $t('workspace.selected_workspaces_deleted'))
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
  <Head :title="$t('workspace.workspaces')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('workspace.workspaces')">
      <template #description>{{ $t('workspace.manage_workspaces_subscribers') }}</template>
    </PageHeader>

    <div class="mt-lg row-px w-full">
      <SelectableBar :count="selectedRecords.length" @close="deselectAllRecords">
        <PureDangerButton v-tooltip="$t('general.delete')" @click="deleteWorkspaces">
          <TrashIcon />
        </PureDangerButton>
      </SelectableBar>

      <Flex class="justify-between">
        <Link :href="route(`${routePrefix}.workspaces.create`)" class="mb-xs sm:mb-0">
          <PrimaryButton>{{ $t('dashboard.create_workspace') }}</PrimaryButton>
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
                  :disabled="!$page.props.workspaces.meta.total"
                />
              </TableCell>
              <TableCell component="th" scope="col"></TableCell>
              <TableCell component="th" scope="col">{{ $t('general.name') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('general.owner') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('general.subscription') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('general.created_at') }}</TableCell>
              <TableCell component="th" scope="col" />
            </TableRow>
          </template>
          <template #body>
            <template v-for="item in $page.props.workspaces.data" :key="item.uuid">
              <WorkspaceItem
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
              </WorkspaceItem>
            </template>
          </template>
        </Table>

        <NoResult v-if="!$page.props.workspaces.meta.total" table />
      </Panel>

      <div v-if="$page.props.workspaces.meta.links.length > 3" class="mt-lg">
        <Pagination :meta="$page.props.workspaces.meta" :links="$page.props.workspaces.links" />
      </div>
    </div>
  </div>
</template>
