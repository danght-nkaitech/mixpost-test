<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { router, useForm } from '@inertiajs/vue3'
import useSubscription from '../../Composables/useSubscription'
import useNotifications from '../../Composables/useNotifications'
import Panel from '../Surface/Panel.vue'
import Flex from '../Layout/Flex.vue'
import DangerButton from '../Button/DangerButton.vue'
import SubscriptionStatusBadge from '../Subscription/SubscriptionStatusBadge.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import DialogModal from '../Modal/DialogModal.vue'
import Checkbox from '../Form/Checkbox.vue'
import Plans from '../Subscription/Plans.vue'
import Error from '../Form/Error.vue'
import Preloader from '../Util/Preloader.vue'
import SubscriptionPlan from '../Subscription/SubscriptionPlan.vue'
import AddGenericSubscription from '../Subscription/AddGenericSubscription.vue'
import SubscriptionEnds from '../Subscription/SubscriptionEnds.vue'
import SectionBorder from '../Surface/SectionBorder.vue'

const props = defineProps({
  workspace: {
    type: Object
  },
  subscription: {
    type: [Object, null]
  },
  currency: {
    type: String,
    default: 'USD'
  },
  billingConfigs: {
    type: Object,
    required: true
  },
  plans: {
    type: Array,
    required: true
  }
})

const { t: $t } = useI18n()

const confirmation = inject('confirmation')
const routePrefix = inject('routePrefix')

const { canChangePlan, canCancel } = useSubscription()
const { notify } = useNotifications()

const formChangePlan = useForm({
  cycle: 'monthly',
  plan_id: null,
  prorate: true,
  billing_immediately: true
})

const changePlanModal = ref(false)

const updateFormChangePlan = data => {
  formChangePlan.cycle = data.cycle
  formChangePlan.plan_id = data.plan_id
}

const openChangePlanModal = () => {
  changePlanModal.value = true
}

const closeChangePlanModal = () => {
  if (formChangePlan.processing) {
    return
  }

  changePlanModal.value = false
  formChangePlan.reset()
}

const changePlan = () => {
  formChangePlan.post(
    route(`${routePrefix}.workspaces.subscription.changePlan`, { workspace: props.workspace.uuid }),
    {
      preserveScroll: true,
      onSuccess() {
        closeChangePlanModal()
      },
      onError() {
        notify('error', $t('error.something_wrong'))
      }
    }
  )
}

const cancel = () => {
  confirmation()
    .title($t('subscription.cancel_sub'))
    .description($t('subscription.confirm_cancel_sub'))
    .destructive()
    .btnCancelName($t('general.dismiss'))
    .btnConfirmName($t('subscription.cancel_sub'))
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.post(
        route(`${routePrefix}.workspaces.subscription.cancel`, { workspace: props.workspace.uuid }),
        {},
        {
          preserveState: true,
          onSuccess() {
            dialog.reset()
          },
          onFinish() {
            dialog.close()
          },
          onError() {
            dialog.isLoading(false)
          }
        }
      )
    })
    .show()
}

const info = ref(null)
const infoModal = ref(false)
const isLoadingInfo = ref(false)

const openInfoModal = () => {
  infoModal.value = true
}

const closeInfoModal = () => {
  infoModal.value = false
}

const getSubscriptionInfo = () => {
  openInfoModal()

  isLoadingInfo.value = true

  axios
    .get(route(`${routePrefix}.workspaces.subscription.info`, { workspace: props.workspace.uuid }))
    .then(response => {
      info.value = response.data
    })
    .catch(() => {
      closeInfoModal()
      notify('error', $t('error.something_wrong'))
    })
    .finally(() => {
      isLoadingInfo.value = false
    })
}

const removeGenericSubscription = () => {
  confirmation()
    .title($t('subscription.remove_sub'))
    .description($t('subscription.confirm_cancel_generic_sub'))
    .destructive()
    .btnCancelName($t('general.dismiss'))
    .btnConfirmName($t('subscription.remove_sub'))
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(
        route(`${routePrefix}.workspaces.subscription.removeGeneric`, {
          workspace: props.workspace.uuid
        }),
        {
          preserveState: true,
          onSuccess() {
            dialog.reset()
          },
          onFinish() {
            dialog.close()
          },
          onError() {
            dialog.isLoading(false)
          }
        }
      )
    })
    .show()
}
</script>
<template>
  <Panel>
    <template #title> {{ $t('subscription.subscription') }}</template>

    <template v-if="subscription">
      <SubscriptionPlan :subscription="subscription" :currency="currency" />

      <SubscriptionStatusBadge :status="subscription.status" class="mt-xs" />

      <SubscriptionEnds :subscription="subscription" />

      <Flex class="mt-lg">
        <template v-if="canChangePlan(subscription.status)">
          <PrimaryButton @click="openChangePlanModal">
            {{ $t('subscription.change_plan') }}
          </PrimaryButton>
        </template>

        <template v-if="canCancel(subscription.status)">
          <DangerButton @click="cancel">
            {{ $t('subscription.cancel_sub') }}
          </DangerButton>
        </template>

        <SecondaryButton @click="getSubscriptionInfo">
          {{ $t('util.debug') }}
        </SecondaryButton>
      </Flex>
    </template>

    <template v-if="workspace.generic_subscription">
      <p class="text-lg font-semibold">{{ workspace.generic_subscription.name }}</p>

      <SubscriptionStatusBadge status="generic" />

      <div
        v-if="!workspace.generic_subscription.free && workspace.generic_subscription.trial_ends_at"
        class="mt-xs"
      >
        <SubscriptionStatusBadge status="trialing" />
        <SubscriptionEnds
          :subscription="{
            status: 'trialing',
            trial_ends_at: workspace.generic_subscription.trial_ends_at
          }"
        />
      </div>
    </template>

    <template v-if="!subscription && !workspace.generic_subscription">
      <div>{{ $t('subscription.no_sub') }}</div>
    </template>

    <SectionBorder />

    <Flex>
      <AddGenericSubscription :workspace="workspace" :plans="plans" />

      <template v-if="workspace.generic_subscription">
        <DangerButton @click="removeGenericSubscription">
          {{ $t('subscription.remove_generic_subscription') }}
        </DangerButton>
      </template>
    </Flex>
  </Panel>

  <DialogModal :show="changePlanModal" max-width="md" @close="closeChangePlanModal">
    <template #header> {{ $t('subscription.change_plan') }}</template>

    <template #body>
      <Plans
        :current-plan="subscription ? subscription.plan : null"
        :plans="plans"
        :billing-configs="billingConfigs"
        @change="updateFormChangePlan"
      />

      <div class="flex mt-lg">
        <Flex col>
          <label>
            <Checkbox v-model:checked="formChangePlan.prorate" />
            {{ $t('finance.prorate') }}
          </label>

          <label>
            <Checkbox v-model:checked="formChangePlan.billing_immediately" />
            {{ $t('finance.bill_immediately') }}
          </label>
        </Flex>
      </div>

      <Error
        v-for="(error, key) in formChangePlan.errors"
        :key="key"
        :message="error"
        class="mt-lg"
      />
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="closeChangePlanModal">
        {{ $t('general.cancel') }}
      </SecondaryButton>

      <PrimaryButton
        :disabled="formChangePlan.processing"
        :is-loading="formChangePlan.processing"
        @click="changePlan"
      >
        {{ $t('general.confirm') }}
      </PrimaryButton>
    </template>
  </DialogModal>

  <DialogModal :show="infoModal" max-width="xl" @close="closeInfoModal">
    <template #header>{{ $t('subscription.debug_sub_information') }}</template>

    <template #body>
      <Preloader v-if="isLoadingInfo" :rounded="true" />

      <template v-if="infoModal && !isLoadingInfo">
        <pre>{{ info }}</pre>
      </template>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="closeInfoModal">
        {{ $t('general.close') }}
      </SecondaryButton>
    </template>
  </DialogModal>
</template>
