import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import useNotifications from './useNotifications'

const useHttpClient = (authPasswordConfirmationInstance = null) => {
  let $t = null

  // Try to get translation function.
  try {
    const { t } = useI18n()
    $t = t
  } catch {
    /* empty */
  }

  const authPasswordConfirmation = authPasswordConfirmationInstance
    ? authPasswordConfirmationInstance
    : inject('authPasswordConfirmation')
  const { notify } = useNotifications()

  const onCatch = (error, callbackOnPasswordConfirmed = null) => {
    if (error.response.status === 422) {
      if (error.response.data.errors.confirm_password) {
        if (typeof authPasswordConfirmation === 'function') {
          authPasswordConfirmation().onConfirm(callbackOnPasswordConfirmed).show()
          return
        }

        authPasswordConfirmation.show()
        return
      }

      notify('error', error.response.data.errors)

      return
    }

    notify('error', !$t ? "It's something wrong. Try again." : $t('error.try_again'))
  }

  return {
    onCatch
  }
}

export default useHttpClient
