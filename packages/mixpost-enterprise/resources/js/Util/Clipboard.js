import useNotifications from '../Composables/useNotifications'

class Clipboard {
  static successMessage = ''
  static errorMessage = ''

  static setSuccessMessage(message) {
    this.successMessage = message
    return this
  }

  static setErrorMessage(message) {
    this.errorMessage = message
    return this
  }

  static async copy(text) {
    const { notify } = useNotifications()

    if (navigator.clipboard) {
      try {
        await navigator.clipboard.writeText(text)
        notify('success', this.successMessage)
      } catch {
        notify('error', this.errorMessage)
      }
    } else {
      notify('error', 'Clipboard API not supported!')
    }
  }
}

export default Clipboard
