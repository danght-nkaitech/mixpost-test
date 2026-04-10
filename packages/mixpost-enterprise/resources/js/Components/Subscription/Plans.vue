<script setup>
import { computed, onMounted, reactive, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import Flex from '../Layout/Flex.vue'
import Radio from '../Form/Radio.vue'
import LabelGroup from '../Surface/LabelGroup.vue'

const { t: $t } = useI18n()

const props = defineProps({
  currentPlan: {
    type: [Object, null],
    default: null
  },
  plans: {
    type: Array,
    required: true
  },
  billingConfigs: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['change'])

const billingCycles = computed(() => {
  if (props.billingConfigs.billing_cycle === 'monthly') {
    return [{ value: 'monthly', name: $t('subscription.monthly') }]
  }

  if (props.billingConfigs.billing_cycle === 'yearly') {
    return [{ value: 'yearly', name: $t('subscription.yearly') }]
  }

  return [
    { value: 'monthly', name: $t('subscription.monthly') },
    { value: 'yearly', name: $t('subscription.yearly') }
  ]
})

const form = reactive(
  props.currentPlan
    ? {
        cycle: props.currentPlan.cycle,
        plan_id: props.currentPlan.id
      }
    : {
        cycle: billingCycles.value[0].value,
        plan_id: props.plans.length > 0 ? props.plans[0].id : null
      }
)

const isYearly = computed(() => form.cycle === 'yearly')

onMounted(() => {
  emit('change', form)
})

watch(form, value => {
  emit('change', value)
})
</script>
<template>
  <div>
    <VerticalGroup>
      <template #title>{{ $t('subscription.billing_cycle') }}</template>

      <Flex :col="true" class="w-full">
        <template v-for="cycle in billingCycles" :key="cycle.value">
          <LabelGroup :active="cycle.value === form.cycle">
            <Radio v-model:checked="form.cycle" :value="cycle.value" />
            {{ $t('subscription.pay_cycle', { cycle: cycle.name }) }}
          </LabelGroup>
        </template>
      </Flex>
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title>
        <div>{{ $t('plan.plan') }}</div>
        <a
          v-if="billingConfigs.plans_page_url"
          :href="billingConfigs.plans_page_url"
          target="_blank"
          class="link ml-xs block font-medium"
        >
          {{
            billingConfigs.plans_page_url_title
              ? billingConfigs.plans_page_url_title
              : $t('general.learn_more')
          }}
        </a>
      </template>

      <Flex :col="true" class="w-full">
        <template v-for="plan in plans" :key="plan.id">
          <LabelGroup :active="plan.id === form.plan_id">
            <span class="flex justify-between items-center">
              <span>
                <Radio v-model:checked="form.plan_id" :value="plan.id" />
                {{ plan.name }} <span v-if="isYearly && !plan.is_free">(yearly)</span>
              </span>

              <template v-if="!plan.is_free">
                <span v-if="isYearly">
                  {{ plan.price.yearly.amount }} {{ billingConfigs.currency }}
                </span>

                <span v-else>{{ plan.price.monthly.amount }} {{ billingConfigs.currency }}</span>
              </template>
            </span>
          </LabelGroup>
        </template>
      </Flex>
    </VerticalGroup>
  </div>
</template>
