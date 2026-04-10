import { STATUS_ACTIVE, STATUS_CANCELED, STATUS_TRIALING } from '../Constants/Subscription'

const useSubscription = () => {
  function canChangePlan(status) {
    return status === STATUS_ACTIVE || status === STATUS_TRIALING
  }

  function canChangePayment(status) {
    return status === STATUS_ACTIVE || status === STATUS_TRIALING
  }

  function canCancel(status) {
    return status !== STATUS_CANCELED
  }

  function isTrialing(status) {
    return status === STATUS_TRIALING
  }

  function isCanceled(status) {
    return status === STATUS_CANCELED
  }

  return {
    canChangePlan,
    canChangePayment,
    canCancel,
    isTrialing,
    isCanceled
  }
}

export default useSubscription
