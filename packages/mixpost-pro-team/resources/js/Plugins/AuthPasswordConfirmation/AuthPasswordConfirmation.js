import { ref } from 'vue'
import { clone } from 'lodash'
import useHttpClient from '../../Composables/useHttpClient'
import AuthPasswordConfirmationComponent from './AuthPasswordConfirmation.vue'

const AuthPasswordConfirmation = {
  install(app) {
    app.component('AuthPasswordConfirmation', AuthPasswordConfirmationComponent)

    const routePrefix = app._context.provides.routePrefix

    const initialData = {
      show: false,
      ensuringPasswordConfirmed: false,
      onConfirm: null
    }

    const data = ref(clone(initialData))

    app.provide('authPasswordConfirmation', () => {
      return {
        data,
        reset() {
          data.value = clone(initialData)
          return this
        },
        show() {
          data.value.show = true
        },
        close() {
          data.value.show = false
        },
        ensureConfirmed() {
          const { onCatch } = useHttpClient(this)

          data.value.ensuringPasswordConfirmed = true

          axios
            .post(route(`${routePrefix}.profile.ensurePasswordConfirmed`))
            .then(() => {
              if (typeof data.value.onConfirm === 'function') {
                data.value.onConfirm()
              }
            })
            .catch(error => onCatch(error))
            .finally(() => (data.value.ensuringPasswordConfirmed = false))
        },
        onConfirm(fnc) {
          data.value.onConfirm = fnc
          return this
        }
      }
    })
  }
}

export default AuthPasswordConfirmation
