<script setup>
import Panel from '../Surface/Panel.vue'
import HorizontalGroup from '../Layout/HorizontalGroup.vue'
import OptionGroup from '../Surface/OptionGroup.vue'

defineProps({
  price: {
    type: Object,
    default: () => ({})
  }
})

const items = ['monthly', 'yearly']
</script>
<template>
  <Panel>
    <template #title>{{ $t('finance.price_info') }}</template>

    <template v-for="item in items" :key="item">
      <OptionGroup>
        <template #title
          ><span class="capitalize">{{ $t(`subscription.${item}`) }}</span></template
        >

        <div class="flex items-center gap-2xl mb-lg last:mb-0">
          <HorizontalGroup class="w-full">
            <template #title>
              <div>{{ $t('finance.amount') }} ({{ $page.props.currency }})</div>
            </template>

            {{ price[item].amount }}
          </HorizontalGroup>

          <HorizontalGroup class="w-full">
            <template #title>
              {{ $t('plan.plan_id') }}
            </template>

            {{ price[item].platform_plan_id }}
          </HorizontalGroup>
        </div>
      </OptionGroup>
    </template>
  </Panel>
</template>
