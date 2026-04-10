import emitter from '@/Services/emitter'
import { convertLaravelErrorsToString } from '../helpers'

const useNotifications = () => {
  const notify = (variant, message, button) => {
    if (typeof message !== 'object') {
      emitter.emit('notify', { variant, message, button })
    }

    if (typeof message === 'object') {
      if (message?.response?.status === 500) {
        emitter.emit('notify', { variant, message: message.response.data.message, button })
        return
      }

      // Convert laravel validation errors to a string
      emitter.emit('notify', { variant, message: convertLaravelErrorsToString(message), button })
    }
  }

  return {
    notify
  }
}

export default useNotifications
