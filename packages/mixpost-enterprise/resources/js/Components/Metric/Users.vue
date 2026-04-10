<script setup>
import Panel from '../Surface/Panel.vue'
import useMetrics from '../../Composables/useMetrics'
import { onMounted, ref } from 'vue'
import Select from '../Form/Select.vue'
import ChartTrend from '../Chart/ChartTrend.vue'

const route = 'users'

const { fetchMetric, rangeDays } = useMetrics()

const result = ref({
  labels: [],
  aggregates: []
})

const params = ref({
  days: '10'
})

const update = async () => {
  result.value = await fetchMetric(route, params.value)
}

onMounted(update)
</script>

<template>
  <Panel>
    <template #title>{{ $t('user.users') }}</template>

    <template #action>
      <Select v-model="params.days" @change="update">
        <option v-for="item in rangeDays" :key="item" :value="item">
          {{ $t('panel.count_days', { count: item }) }}
        </option>
      </Select>
    </template>

    <ChartTrend :label="$t('user.users')" :labels="result.labels" :aggregates="result.aggregates" />
  </Panel>
</template>
