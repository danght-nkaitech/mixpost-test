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

const getMetricCount = value => {
  return Object.prototype.hasOwnProperty.call(props.data.metrics, value)
    ? props.data.metrics[value]
    : 0
}

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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-sm">
      <Panel>
        <template #title
          ><span v-tooltip="$t('report.posts_liked')"> {{ $t('report.likes') }}</span>
        </template>
        <div class="font-bold text-primary-500 text-2xl">{{ getMetricCount('likes') }}</div>
      </Panel>

      <Panel>
        <template #title
          ><span v-tooltip="$t('service.instagram.report.number_comments_posts')">{{
            $t('report.comments')
          }}</span>
        </template>
        <div class="font-bold text-primary-500 text-2xl">{{ getMetricCount('comments') }}</div>
      </Panel>

      <Panel>
        <template #title
          ><span v-tooltip="$t('service.instagram.report.number_new_followers')"
            >{{ $t('service.instagram.report.follower_count') }}
          </span>
        </template>
        <div class="font-bold text-primary-500 text-2xl">
          {{ getMetricCount('follower_count') }}
        </div>
      </Panel>

      <Panel>
        <template #title
          ><span v-tooltip="$t('service.instagram.report.number_reach')">{{
            $t('service.instagram.report.reach')
          }}</span>
        </template>
        <div class="font-bold text-primary-500 text-2xl">{{ getMetricCount('reach') }}</div>
      </Panel>
    </div>
  </div>

  <div class="row-px mt-2xl">
    <Panel>
      <template #title>{{ $t('report.audience') }}</template>
      <template #description>{{ $t('report.followers_per_day') }}</template>
      <ChartTrend
        :label="$t('report.followers')"
        :labels="chartData.labels"
        :aggregates="chartData.aggregates"
      />
    </Panel>
  </div>
</template>
