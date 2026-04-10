import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import NProgress from 'nprogress'
import useNotifications from '@/Composables/useNotifications.js'

const useMetrics = () => {
  const { t: $t } = useI18n()

  const routePrefix = inject('routePrefix')

  const { notify } = useNotifications()

  const fetchMetric = (routeMetric, params = {}) => {
    NProgress.start()

    return new Promise((resolve, reject) => {
      axios
        .get(route(`${routePrefix}.metrics.${routeMetric}`), {
          params
        })
        .then(function (response) {
          resolve({
            labels: getLabels(response.data),
            aggregates: getAggregates(response.data)
          })
        })
        .catch(() => {
          notify('error', $t('error.try_again'))
          reject()
        })
        .finally(() => {
          NProgress.done()
        })
    })
  }

  const getLabels = data => {
    return data.map(item => {
      return item.date_readable
    })
  }

  const getAggregates = data => {
    return data.map(item => {
      return item.aggregate
    })
  }

  const rangeDays = ['10', '30', '60', '90']

  return {
    fetchMetric,
    getLabels,
    getAggregates,
    rangeDays
  }
}

export default useMetrics
