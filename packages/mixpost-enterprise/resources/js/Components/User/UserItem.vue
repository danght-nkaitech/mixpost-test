<script setup>
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import UserItemAction from '@/Components/User/UserItemAction.vue'
import Avatar from '@/Components/DataDisplay/Avatar.vue'
import Indicators from '../User/Indicators.vue'
import Flex from '../Layout/Flex.vue'

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
      <Avatar :name="item.name" />
    </TableCell>

    <TableCell>
      <Flex class="sm:items-center">
        <div>{{ item.name }}</div>
        <Indicators :user="item" />
      </Flex>
    </TableCell>

    <TableCell>
      {{ item.email }}
    </TableCell>

    <TableCell>
      {{ item.created_at }}
    </TableCell>

    <TableCell>
      <UserItemAction :item-id="item.id" />
    </TableCell>
  </TableRow>
</template>
