<script setup>
import Panel from '../Surface/Panel.vue'
import Input from '../Form/Input.vue'
import HorizontalGroup from '../Layout/HorizontalGroup.vue'
import InputLength from '../Form/InputLength.vue'
import OptionGroup from '../Surface/OptionGroup.vue'

defineProps({
  price: {
    type: Object,
    default: () => ({})
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const items = ['monthly', 'yearly']
</script>
<template>
  <Panel>
    <template #title> {{ $t('finance.price_info') }}</template>

    <template v-for="item in items" :key="item">
      <OptionGroup>
        <template #title
          ><span class="capitalize">{{ $t(`subscription.${item}`) }}</span></template
        >
        <div class="flex items-center gap-2xl mb-lg last:mb-0">
          <HorizontalGroup>
            <template #title>
              <label :for="`price-${item}`"
                >{{ $t('finance.amount') }} ({{ $page.props.currency }})</label
              >
            </template>

            <InputLength
              :id="`price-${item}`"
              v-model="price[item].amount"
              :error="errors[`price.${item}.amount`]"
              class="w-full"
              required
            />
          </HorizontalGroup>

          <HorizontalGroup>
            <template #title>
              <label :for="`platform_plan_id-${item}`">{{ $t('plan.plan_id') }} </label>
            </template>

            <Input
              :id="`platform_plan_id-${item}`"
              v-model="price[item].platform_plan_id"
              type="text"
              :error="errors[`price.${item}.platform_plan_id`]"
              class="w-full"
              required
            />
          </HorizontalGroup>
        </div>
      </OptionGroup>
    </template>
  </Panel>
</template>
