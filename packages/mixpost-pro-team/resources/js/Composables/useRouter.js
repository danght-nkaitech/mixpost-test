import useNotifications from './useNotifications'
import { inject } from 'vue'

const useRouter = () => {
  const { notify } = useNotifications()
  const authPasswordConfirmation = inject('authPasswordConfirmation')

  const onError = (errors, callbackOnPasswordConfirmed = null) => {
    if (errors.confirm_password) {
      authPasswordConfirmation().onConfirm(callbackOnPasswordConfirmed).show()
      return
    }

    notify('error', errors)
  }

  return {
    onError
  }
}

export default useRouter
