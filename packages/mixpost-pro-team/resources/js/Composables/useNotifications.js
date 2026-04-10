import emitter from '@/Services/emitter'
import { convertLaravelErrorsToString } from '../helpers'

const useNotifications = () => {
  const notify = (variant, message, button) => {
    if (typeof message !== 'object') {
      emitter.emit('notify', { variant, message, button })
    }

    if (axios.isAxiosError(message)) {
      if (message.response.status === 422) {
        emitter.emit('notify', {
          variant,
          message: convertLaravelErrorsToString(message.response.data.errors),
          button
        })
      }

      if (message.response.status === 500) {
        emitter.emit('notify', { variant, message: message.message, button })
      }
    }
  }

  return {
    notify
  }
}

export default useNotifications
