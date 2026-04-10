import { computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import useNotifications from './useNotifications.js'
import { convertLaravelErrorsToString } from '../helpers.js'

const useRequestErrorsHandler = () => {
  const { notify } = useNotifications()

  const errors = computed(() => {
    return usePage().props.errors
  })

  watch(
    () => errors.value,
    errors => {
      notify('error', convertLaravelErrorsToString(errors))
    }
  )
}

export default useRequestErrorsHandler
