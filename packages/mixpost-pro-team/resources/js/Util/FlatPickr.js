import { convertTime12to24 } from '../helpers'

/**
 * Capture with the manually entered time value upon keyup events
 */
export function captureTimeValueTo24(ref, timeFormat) {
  const hour = ref.querySelector('.flatpickr-hour').value
  const minutes = ref.querySelector('.flatpickr-minute').value

  if (timeFormat === 24) {
    return `${hour}:${minutes}`
  }

  if (timeFormat === 12) {
    const ampm = ref.querySelector('.flatpickr-am-pm').innerText

    return convertTime12to24(`${hour}:${minutes} ${ampm}`)
  }
}
