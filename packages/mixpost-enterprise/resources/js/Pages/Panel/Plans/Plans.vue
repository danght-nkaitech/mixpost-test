<script setup>
import { inject } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import Table from '../../../Components/DataDisplay/Table.vue'
import TableRow from '../../../Components/DataDisplay/TableRow.vue'
import TableCell from '../../../Components/DataDisplay/TableCell.vue'
import NoResult from '../../../Components/Util/NoResult.vue'
import PlanItemAction from '../../../Components/Plan/PlanItemAction.vue'
import Badge from '../../../Components/DataDisplay/Badge.vue'

const routePrefix = inject('routePrefix')
</script>
<template>
  <Head :title="$t('plan.plans')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('plan.plans')">
      <template #description>
        {{ $t('plan.define_plans') }}
      </template>
    </PageHeader>

    <div class="mt-lg row-px w-full">
      <Link :href="route(`${routePrefix}.plans.create`)" class="mb-xs sm:mb-0">
        <PrimaryButton>{{ $t('plan.create_plan') }}</PrimaryButton>
      </Link>

      <Panel :with-padding="false" class="mt-lg">
        <Table>
          <template #head>
            <TableRow>
              <TableCell component="th" scope="col">{{ $t('general.name') }}</TableCell>
              <TableCell component="th" scope="col"
                >{{ $t('finance.price') }} ({{ $page.props.currency }})
              </TableCell>
              <TableCell component="th" scope="col">{{ $t('plan.status') }}</TableCell>
              <TableCell component="th" scope="col" />
            </TableRow>
          </template>
          <template #body>
            <template v-for="item in $page.props.plans.data" :key="item.id">
              <TableRow :hoverable="true">
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>
                  <Badge v-if="item.is_free" variant="info">{{ $t('general.free') }}</Badge>
                  <div v-else>
                    <div>Monthly: {{ item.price.monthly.amount }}</div>
                    <div>Yearly: {{ item.price.yearly.amount }}</div>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge v-if="item.enabled" variant="success">{{ $t('general.enabled') }}</Badge>
                  <Badge v-else variant="danger">{{ $t('general.disabled') }}</Badge>
                </TableCell>
                <TableCell>
                  <PlanItemAction :item-id="item.id" />
                </TableCell>
              </TableRow>
            </template>
          </template>
        </Table>

        <NoResult v-if="!$page.props.plans.data.length" class="p-md">
          {{ $t('plan.no_plans') }}
        </NoResult>
      </Panel>
    </div>
  </div>
</template>
