<script setup>
import { Head } from '@inertiajs/vue3'
import { inject } from 'vue'
import WorkspaceLayout from '@/Layouts/Workspace.vue'
import Panel from '@/Components/Surface/Panel.vue'
import Table from '@/Components/DataDisplay/Table.vue'
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import NoResult from '../../../Components/Util/NoResult.vue'
import Pagination from '../../../Components/Navigation/Pagination.vue'
import useWorkspace from '../../../Composables/useWorkspace'
import PageHeader from '../../../Components/DataDisplay/PageHeader.vue'

defineOptions({ layout: WorkspaceLayout })

const routePrefix = inject('routePrefix')

defineProps({
  receipts: {
    type: Object
  }
})

const { activeWorkspaceId } = useWorkspace()
</script>
<template>
  <Head :title="$t('sidebar.receipts')" />

  <PageHeader :title="$t('sidebar.receipts')" :with-padding-x="false" />

  <div class="w-full mx-auto">
    <Panel>
      <Table>
        <template #head>
          <TableRow>
            <TableCell component="th" scope="col" class="w-80"></TableCell>
            <TableCell component="th" scope="col">{{ $t('finance.billing_date') }}</TableCell>
            <TableCell component="th" scope="col"> {{ $t('finance.amount') }}</TableCell>
            <TableCell component="th" scope="col" />
          </TableRow>
        </template>
        <template #body>
          <template v-for="item in receipts.data" :key="item.id">
            <TableRow :hoverable="true">
              <TableCell class="w-80">{{ item.description }}</TableCell>
              <TableCell>{{ item.created_at }}</TableCell>
              <TableCell>{{ item.amount }} {{ item.currency }}</TableCell>
              <TableCell>
                <a
                  v-if="item.receipt_url"
                  :href="item.receipt_url"
                  target="_blank"
                  class="link-primary"
                >
                  {{ $t('general.download') }}
                </a>

                <a
                  v-else
                  :href="
                    route(`${routePrefix}.workspace.receipts.download`, {
                      workspace: activeWorkspaceId,
                      receipt: item.uuid
                    })
                  "
                  target="_blank"
                  class="link-primary"
                >
                  {{ $t('general.download') }}
                </a>
              </TableCell>
            </TableRow>
          </template>
        </template>
      </Table>

      <NoResult v-if="!receipts.meta.total" />
    </Panel>

    <div v-if="receipts.meta.links.length > 3" class="mt-lg">
      <Pagination :meta="receipts.meta" :links="receipts.links" />
    </div>
  </div>
</template>
