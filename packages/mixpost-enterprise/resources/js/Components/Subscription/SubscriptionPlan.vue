<script setup>
import useAuth from '../../Composables/useAuth'
import Flex from '../Layout/Flex.vue'
import Exclamation from '../../Icons/Exclamation.vue'
import Collapse from '../Surface/Collapse.vue'

const { user } = useAuth()

defineProps({
  subscription: {
    type: Object,
    required: true
  },
  currency: {
    type: String,
    default: 'USD'
  }
})
</script>
<template>
  <div v-if="subscription.plan">
    <div class="text-lg font-semibold">{{ subscription.plan.name }}</div>
    <div class="text-md">
      {{ subscription.plan.amount }} {{ currency }}
      <span class="lowercase">{{ $t(`subscription.${subscription.plan.cycle}`) }}</span>
    </div>
  </div>

  <div v-else>
    <div class="text-lg font-semibold">
      <Flex>
        <Exclamation class="destructive" />
        {{ $t('plan.deleted_plan') }}
      </Flex>
    </div>

    <template v-if="user.is_admin">
      <Collapse class="mt-xs">
        <template #title> {{ $t('util.debug') }}</template>
        {{ subscription }}
      </Collapse>
    </template>
  </div>
</template>
