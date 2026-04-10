<script setup>
import { useI18n } from 'vue-i18n'
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import WebhookItemAction from './WebhookItemAction.vue'
import Badge from '../DataDisplay/Badge.vue'

const { t: $t } = useI18n()

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const lastStatusVariant = () => {
  switch (props.item.last_delivery_status) {
    case 'SUCCESS':
      return 'success'
    case 'ERROR':
      return 'error'
    default:
      return 'neutral'
  }
}

const lastStatusText = () => {
  switch (props.item.last_delivery_status) {
    case 'SUCCESS':
      return $t('webhook.last_delivery_succeeded')
    case 'ERROR':
      return $t('webhook.last_delivery_failed')
    default:
      return $t('webhook.never_triggered')
  }
}
</script>
<template>
  <TableRow :hoverable="true">
    <TableCell class="w-10">
      <slot name="checkbox" />
    </TableCell>

    <TableCell>
      <Badge
        v-tooltip="lastStatusText()"
        :variant="lastStatusVariant()"
        class="w-5 h-5 rounded-full!"
      />
    </TableCell>

    <TableCell>
      {{ item.name }}
    </TableCell>

    <TableCell>
      <span class="text-gray-500"
        >{{ `${item.callback_url.slice(0, 60)}`
        }}{{ item.callback_url.length > 60 ? '...' : '' }}</span
      >
    </TableCell>

    <TableCell>
      <Badge :variant="item.active ? 'success' : 'error'">
        {{ item.active ? $t('general.active') : $t('general.inactive') }}
      </Badge>
    </TableCell>

    <TableCell>
      <WebhookItemAction :item-id="item.id" />
    </TableCell>
  </TableRow>
</template>
