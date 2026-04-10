<script setup>
import Badge from '../DataDisplay/Badge.vue'
import useSubscription from '../../Composables/useSubscription'

defineProps({
  subscription: {
    type: Object,
    required: true
  }
})

const { isTrialing, isCanceled } = useSubscription()
</script>
<template>
  <Badge v-if="isTrialing(subscription.status) && subscription.trial_ends_at" class="ml-xs">
    {{ $t('subscription.ends_at', { date: subscription.trial_ends_at }) }}
  </Badge>

  <Badge v-if="isCanceled(subscription.status) && subscription.ends_at" class="ml-xs">
    {{ $t('subscription.ends_at', { date: subscription.ends_at }) }}
  </Badge>
</template>
