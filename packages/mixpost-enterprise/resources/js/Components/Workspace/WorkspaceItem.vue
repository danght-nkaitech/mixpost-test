<script setup>
import { computed } from 'vue'
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import Avatar from '@/Components/DataDisplay/Avatar.vue'
import WorkspaceItemAction from './WorkspaceItemAction.vue'
import UserLink from '../User/UserLink.vue'
import SubscriptionStatusBadge from '../Subscription/SubscriptionStatusBadge.vue'
import Flex from '../Layout/Flex.vue'
import Indicators from './Indicators.vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const subscription = computed(() => {
  return props.item.subscriptions[0] || null
})
</script>
<template>
  <TableRow :hoverable="true">
    <TableCell class="w-10">
      <slot name="checkbox" />
    </TableCell>

    <TableCell>
      <Avatar :background-color="item.hex_color" :name="item.name" rounded-class="rounded-lg" />
    </TableCell>

    <TableCell>
      <Flex class="sm:items-center">
        <span>{{ item.name }}</span>

        <Indicators :workspace="item" />
      </Flex>
    </TableCell>

    <TableCell>
      <template v-if="item.owner">
        <UserLink :id="item.owner.id" :name="item.owner.name" />
      </template>
    </TableCell>

    <TableCell>
      <template v-if="subscription">
        <span class="inline-flex mr-xs">{{
          subscription.plan ? subscription.plan.name : subscription.name
        }}</span>

        <SubscriptionStatusBadge :status="subscription.status" />
      </template>

      <template v-else-if="item.generic_subscription">
        <span class="inline-flex mr-xs">
          {{ item.generic_subscription.name }}
        </span>

        <SubscriptionStatusBadge status="generic" />
      </template>

      <template v-else> - </template>
    </TableCell>

    <TableCell>
      {{ item.created_at }}
    </TableCell>

    <TableCell>
      <WorkspaceItemAction :item-id="item.uuid" />
    </TableCell>
  </TableRow>
</template>
