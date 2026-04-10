<script setup>
import { Head } from '@inertiajs/vue3'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import WorkspaceLink from '../../../Components/Workspace/WorkspaceLink.vue'
import ReceiptAction from '../../../Components/Receipt/ReceiptAction.vue'

defineProps({
  receipt: {
    type: Object
  }
})
</script>
<template>
  <Head :title="$t('finance.view_receipt')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('finance.view_receipt')">
      <template #description>
        {{ receipt.invoice_number }}
      </template>

      <ReceiptAction :receipt="receipt" :view="false" />
    </PageHeader>

    <div class="row-px">
      <Panel>
        <template #title>{{ $t('general.details') }}</template>

        <div class="form-field">
          <HorizontalGroup>
            <template #title>
              {{ $t('workspace.workspace') }}
            </template>

            <template v-if="receipt.workspace">
              <WorkspaceLink :name="receipt.workspace.name" :uuid="receipt.workspace.uuid" />
            </template>
            <template v-else>-</template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('finance.invoice_number') }}
            </template>

            {{ receipt.invoice_number }}
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('finance.transaction_id') }}
            </template>

            {{ receipt.transaction_id }}
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('finance.amount') }}
            </template>

            {{ receipt.amount }} {{ receipt.currency }}
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('finance.tax') }}
            </template>

            {{ receipt.tax }} {{ receipt.currency }}
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('general.description') }}
            </template>

            {{ receipt.description }}
          </HorizontalGroup>

          <HorizontalGroup class="mt-md">
            <template #title>
              {{ $t('finance.billing_date') }}
            </template>

            {{ receipt.paid_at }}
          </HorizontalGroup>
        </div>
      </Panel>
    </div>
  </div>
</template>
