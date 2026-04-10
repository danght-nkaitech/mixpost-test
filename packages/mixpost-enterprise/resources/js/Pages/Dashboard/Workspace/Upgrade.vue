<script setup>
import { computed, inject, onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, router, useForm } from '@inertiajs/vue3'
import useWorkspace from '../../../Composables/useWorkspace'
import useSubscription from '../../../Composables/useSubscription'
import useNotifications from '../../../Composables/useNotifications'
import { convertLaravelErrorsToString, loadScriptsFromString } from '../../../helpers'
import WorkspaceLayout from '@/Layouts/Workspace.vue'
import Panel from '@/Components/Surface/Panel.vue'
import Error from '../../../Components/Form/Error.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Plans from '../../../Components/Subscription/Plans.vue'
import ArrowRight from '../../../Icons/ArrowRight.vue'
import DialogModal from '../../../Components/Modal/DialogModal.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import Preloader from '../../../Components/Util/Preloader.vue'
import PureButtonLink from '../../../Components/Button/PureButtonLink.vue'
import ArrowLeft from '../../../Icons/ArrowLeft.vue'
import Input from '../../../Components/Form/Input.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'

defineOptions({ layout: WorkspaceLayout })

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const props = defineProps({
  workspace: {
    type: Object,
    required: true
  },
  subscription: {
    type: [Object, null],
    required: true
  },
  billingConfigs: {
    type: Object,
    required: true
  },
  plans: {
    type: Array,
    required: true
  },
  paymentSupportTrialing: {
    type: Boolean,
    required: true
  },
  paymentSupportCoupon: {
    type: Boolean,
    required: true
  }
})

const form = useForm({
  cycle: 'monthly',
  plan_id: null,
  coupon: ''
})

const paymentMethod = ref(null)
const { activeWorkspaceId } = useWorkspace()
const { notify } = useNotifications()

const updateForm = data => {
  form.cycle = data.cycle
  form.plan_id = data.plan_id
}

const getTrialDays = () => {
  if (!props.billingConfigs.generic_trial && !props.paymentSupportTrialing) {
    return 0
  }

  if (props.subscription) {
    return 0
  }

  if (props.workspace.generic_subscription && props.workspace.generic_subscription.has_trial) {
    return 0
  }

  return parseInt(props.billingConfigs.trial_days)
}

const { isCanceled } = useSubscription()

const buttonLabel = computed(() => {
  const trialDays = getTrialDays()

  if (trialDays) {
    return $t('subscription.start_trial', { days: trialDays })
  }

  if (props.subscription && !isCanceled(props.subscription.status)) {
    return $t('subscription.change_plan')
  }

  return $t('general.subscribe')
})

const newSubscriptionIsLoading = ref(false)
const paymentDetailsIsLoading = ref(false)
const paymentDetailsModal = ref(false)

const openPaymentDetailsModal = () => {
  paymentDetailsModal.value = true
}
const closePaymentDetailsModal = () => {
  paymentDetailsModal.value = false
}

const handleClosePaymentDetailsModalEvent = () => {
  closePaymentDetailsModal()
}

onMounted(() => {
  document.addEventListener('closePaymentDetailsModal', handleClosePaymentDetailsModalEvent)
})

onBeforeUnmount(() => {
  document.removeEventListener('closePaymentDetailsModal', handleClosePaymentDetailsModalEvent)
})

const loadedScripts = shallowRef([])

const newSubscription = () => {
  newSubscriptionIsLoading.value = true

  loadedScripts.value.forEach(script => {
    script.remove()
  })

  axios
    .post(
      route(`${routePrefix}.workspace.subscription.new`, { workspace: activeWorkspaceId.value }),
      form.data()
    )
    .then(response => {
      if (response.data.redirect_to) {
        window.location.href = response.data.redirect_to
        return
      }

      paymentDetailsIsLoading.value = true

      openPaymentDetailsModal()

      setTimeout(() => {
        loadedScripts.value = loadScriptsFromString(response.data)
        paymentMethod.value.innerHTML = response.data
        paymentDetailsIsLoading.value = false
      }, 1000)
    })
    .finally(() => {
      newSubscriptionIsLoading.value = false
    })
    .catch(error => {
      paymentDetailsIsLoading.value = false

      if (error.response.status === 409) {
        notify('success', error.response.data.message)
        router.visit(
          route(`${routePrefix}.workspace.billing`, { workspace: activeWorkspaceId.value })
        )
        return
      }

      if (error.response.status === 422) {
        form.setError('error', convertLaravelErrorsToString(error.response.data.errors))
      }

      notify('error', $t('error.something_wrong'))
    })
}

const swapSubscriptionPlan = () => {
  form.post(
    route(`${routePrefix}.workspace.subscription.changePlan`, {
      workspace: activeWorkspaceId.value
    }),
    {
      preserveScroll: true,
      onError() {
        notify('error', $t('error.something_wrong'))
      }
    }
  )
}

const { canChangePlan } = useSubscription()

const submit = () => {
  form.clearErrors()

  if (props.subscription && canChangePlan(props.subscription.status)) {
    swapSubscriptionPlan()
  } else {
    newSubscription()
  }
}

const title = () => {
  if (props.subscription && !isCanceled(props.subscription.status)) {
    return $t('subscription.change_sub_plan')
  }

  return $t('general.subscribe')
}
</script>
<template>
  <Head :title="title()" />

  <div class="w-full mx-auto">
    <Panel>
      <template #title>
        {{ title() }}
      </template>

      <Plans
        :current-plan="subscription ? subscription.plan : null"
        :billing-configs="billingConfigs"
        :plans="plans"
        @change="updateForm"
      />

      <div>
        <Error v-for="(error, key) in form.errors" :key="key" :message="error" class="mt-lg" />
      </div>
    </Panel>

    <template v-if="paymentSupportCoupon">
      <Panel class="mt-lg">
        <template #title>
          {{ $t('subscription.use_coupon') }}
        </template>

        <HorizontalGroup>
          <Input
            v-model="form.coupon"
            name="coupon"
            type="text"
            :placeholder="$t('subscription.insert_coupon')"
          />
        </HorizontalGroup>
      </Panel>
    </template>

    <template v-if="subscription">
      <PureButtonLink
        :href="route(`${routePrefix}.workspace.billing`, { workspace: activeWorkspaceId })"
        class="mt-lg mr-md"
      >
        <template #icon>
          <ArrowLeft />
        </template>
        <span v-if="!isCanceled(subscription.status)" class="link-primary">{{
          $t('plan.keep_old_plan')
        }}</span>
        <span v-if="isCanceled(subscription.status)" class="link-primary">{{
          $t('dashboard.back_billing')
        }}</span>
      </PureButtonLink>
    </template>

    <PrimaryButton
      :disabled="newSubscriptionIsLoading || form.processing"
      :is-loading="newSubscriptionIsLoading || form.processing"
      size="sm"
      class="mt-lg"
      @click="submit"
    >
      <template #icon>
        <ArrowRight />
      </template>
      {{ buttonLabel }}
    </PrimaryButton>
  </div>

  <DialogModal
    :show="paymentDetailsModal"
    max-width="sm"
    :closeable="false"
    @close="closePaymentDetailsModal"
  >
    <template #header>
      {{ $t('dashboard.payment_details') }}
    </template>

    <template #body>
      <Preloader v-if="paymentDetailsIsLoading" :rounded="true" />
      <div ref="paymentMethod" class="mt-1" />
    </template>

    <template #footer>
      <SecondaryButton @click="closePaymentDetailsModal">{{
        $t('general.cancel')
      }}</SecondaryButton>
    </template>
  </DialogModal>
</template>
