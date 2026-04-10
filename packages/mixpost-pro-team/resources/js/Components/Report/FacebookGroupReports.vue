<script setup>
import Panel from '@/Components/Surface/Panel.vue'
import { computed } from 'vue'
import ChartTrend from '../Chart/ChartTrend.vue'

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  isLoading: {
    type: Boolean,
    required: true
  }
})

const getAudienceData = value => {
  return Object.prototype.hasOwnProperty.call(props.data.audience, value)
    ? props.data.audience[value]
    : []
}

const chartData = computed(() => {
  return {
    labels: getAudienceData('labels'),
    aggregates: getAudienceData('values')
  }
})
</script>
<template>
  <div class="row-px mt-2xl">
    <Panel>
      <template #title>{{ $t('report.audience') }}</template>
      <template #description>{{ $t('service.facebook.report.number_members_per_day') }}</template>
      <ChartTrend
        :label="$t('report.members')"
        :labels="chartData.labels"
        :aggregates="chartData.aggregates"
      />
    </Panel>
  </div>
</template>
