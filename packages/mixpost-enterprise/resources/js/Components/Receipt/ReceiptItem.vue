<script setup>
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import ReceiptItemAction from './ReceiptItemAction.vue'
import WorkspaceLink from '../Workspace/WorkspaceLink.vue'

defineProps({
  item: {
    type: Object,
    required: true
  }
})
</script>
<template>
  <TableRow :hoverable="true">
    <TableCell class="w-10">
      <slot name="checkbox" />
    </TableCell>

    <TableCell>
      {{ item.invoice_number }}
    </TableCell>

    <TableCell>
      <template v-if="item.workspace">
        <WorkspaceLink :name="item.workspace.name" :uuid="item.workspace.uuid" />
      </template>

      <template v-else> - </template>
    </TableCell>

    <TableCell>
      {{ item.amount }}
    </TableCell>

    <TableCell>
      {{ item.tax }}
    </TableCell>

    <TableCell>
      {{ item.currency }}
    </TableCell>

    <TableCell>
      {{ item.paid_at }}
    </TableCell>

    <TableCell>
      <ReceiptItemAction :item-id="item.uuid" />
    </TableCell>
  </TableRow>
</template>
