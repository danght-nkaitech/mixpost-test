<script setup>
import { Head } from '@inertiajs/vue3'
import { find } from 'lodash'
import { computed } from 'vue'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import FeatureLimit from '../../../Components/FeatureLimit/FeatureLimit.vue'
import NoResult from '../../../Components/Util/NoResult.vue'
import Star from '../../../Icons/Star.vue'
import Badge from '../../../Components/DataDisplay/Badge.vue'
import PlanPriceView from '../../../Components/Plan/PlanPriceView.vue'
import Actions from '../../../Components/Plan/Actions.vue'

const props = defineProps({
  featureLimitResources: {
    type: Array,
    required: true
  },
  plan: {
    type: Object,
    required: false
  }
})

const items = computed(() => {
  return props.plan.limits
    .map(featureLimit => {
      const resource = find(props.featureLimitResources, { code: featureLimit.code })

      if (!resource) {
        return null
      }

      return {
        resource,
        featureLimit
      }
    })
    .filter(item => item !== null)
})
</script>
<template>
  <Head :title="$t('plan.view_plan')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('plan.view_plan')">
      <template #description>
        {{ plan.name }}
      </template>

      <Actions :plan="plan" :view="false" />
    </PageHeader>

    <div class="mt-lg row-px w-full">
      <Panel>
        <template #title>{{ $t('general.detail') }}</template>

        <div class="w-full form-field">
          <div>
            <Badge v-if="plan.is_free" variant="info">{{ $t('plan.free_plan') }}</Badge>
          </div>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('general.name') }}
            </template>

            {{ plan.name }}
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('general.status') }}
            </template>

            <Badge v-if="plan.enabled" variant="success">{{ $t('general.enabled') }}</Badge>
            <Badge v-else variant="danger">{{ $t('general.disabled') }}</Badge>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('general.sort_order') }}
            </template>

            {{ plan.sort_order }}
          </HorizontalGroup>
        </div>
      </Panel>

      <template v-if="!plan.is_free">
        <PlanPriceView :price="plan.price" class="mt-lg" />
      </template>

      <Panel class="mt-lg">
        <template #title>
          <div class="flex items-center">
            <Star class="mr-xs" />
            {{ $t('plan.feature_limits') }}
          </div>
        </template>

        <div class="w-full">
          <div class="flex flex-col gap-md w-full">
            <template v-for="(item, index) in items" :key="`${item.resource.code}-${index}`">
              <FeatureLimit
                :resource="item.resource"
                :form="item.featureLimit.form"
                :read-only="true"
              />
            </template>

            <NoResult v-if="!plan.limits.length" />
          </div>
        </div>
      </Panel>
    </div>
  </div>
</template>
