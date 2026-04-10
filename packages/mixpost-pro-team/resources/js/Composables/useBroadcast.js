import { usePage } from '@inertiajs/vue3'
import Pusher from 'pusher-js'
import { default as LaravelEcho } from 'laravel-echo'
import { toRawIfProxy } from '../helpers'

const useBroadcast = () => {
  const options = usePage().props.broadcast

  const openBroadcast = () => {
    if (!options) {
      return
    }

    window.Pusher = Pusher

    window.Echo = new LaravelEcho({
      ...toRawIfProxy(options),
      ...{
        namespace: 'Inovector.Mixpost.Events'
      }
    })

    // window.Echo.connector.pusher.connection.bind('state_change', function (states) {
    //     console.log('Connection state changed:', states);
    // });
    //
    // window.Echo.connector.pusher.connection.bind('error', function (err) {
    //     console.error('Connection error:', err);
    // });
  }

  const closeBroadcast = () => {
    window.Echo?.disconnect()
    window.Echo = null
  }

  const connectBroadcastPrivate = channel => {
    return window.Echo?.private(channel)
  }

  const leaveBroadcastChannel = channel => {
    window.Echo?.leave(channel)
  }

  return {
    openBroadcast,
    closeBroadcast,
    connectBroadcastPrivate,
    leaveBroadcastChannel
  }
}

export default useBroadcast
