<script setup>
import ListGroup from '../DataDisplay/ListGroup.vue'
import ListItem from '../DataDisplay/ListItem.vue'

defineProps({
  paymentMethod: {
    type: Object,
    required: true
  }
})
</script>
<template>
  <ListGroup v-if="paymentMethod.type">
    <ListItem>
      <span class="font-bold">{{ $t('general.type') }}</span
      >: <span class="capitalize"> {{ paymentMethod.type }}</span>
    </ListItem>

    <ListItem v-if="paymentMethod.card_brand">
      <span class="font-bold">{{ $t('util.brand') }}</span
      >: <span class="capitalize">{{ paymentMethod.card_brand }}</span>
    </ListItem>

    <ListItem v-if="paymentMethod.card_last_four">
      <span class="font-bold">{{ $t('subscription.ending_in') }}</span
      >: {{ paymentMethod.card_last_four }}
    </ListItem>

    <ListItem v-if="paymentMethod.card_expires">
      <span class="font-bold">{{ $t('subscription.expires_on') }}</span
      >: {{ paymentMethod.card_expires }}
    </ListItem>
  </ListGroup>
</template>
