<script setup>
import { inject, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, Link, router } from '@inertiajs/vue3'
import useWorkspace from '../../../Composables/useWorkspace'
import useSubscription from '../../../Composables/useSubscription'
import useRouter from '../../../Composables/useRouter'
import WorkspaceLayout from '@/Layouts/Workspace.vue'
import Panel from '@/Components/Surface/Panel.vue'
import DangerButton from '../../../Components/Button/DangerButton.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Preloader from '../../../Components/Util/Preloader.vue'
import SubscriptionStatusBadge from '../../../Components/Subscription/SubscriptionStatusBadge.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import PaymentDetails from '../../../Components/Subscription/PaymentDetails.vue'
import SubscriptionPlan from '../../../Components/Subscription/SubscriptionPlan.vue'
import SubscriptionEnds from '../../../Components/Subscription/SubscriptionEnds.vue'
import PageHeader from '../../../Components/DataDisplay/PageHeader.vue'

defineOptions({ layout: WorkspaceLayout })

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')
const { canCancel, isCanceled } = useSubscription()
const { onError } = useRouter()

const props = defineProps({
  workspace: {
    required: true,
    type: Object
  },
  subscription: {
    required: true,
    type: [Object, null]
  },
  currency: {
    type: String,
    default: 'USD'
  },
  hasDelay: {
    type: Boolean,
    default: false
  },
  freePlanExists: {
    type: Boolean,
    default: false
  }
})

const { activeWorkspaceId } = useWorkspace()

const cancelSubscription = () => {
  confirmation()
    .title($t('subscription.cancel_sub'))
    .description($t('subscription.confirm_cancel_sub'))
    .destructive()
    .btnCancelName($t('general.dismiss'))
    .btnConfirmName($t('subscription.cancel_sub'))
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.post(
        route(`${routePrefix}.workspace.subscription.cancel`, {
          workspace: activeWorkspaceId.value
        }),
        {},
        {
          preserveState: true,
          onSuccess() {
            dialog.reset()
          },
          onFinish() {
            dialog.isLoading(false)
          },
          onError
        }
      )
    })
    .show()
}

const downgradeSubscriptionToFreePlan = () => {
  confirmation()
    .title($t('subscription.downgrade_free_plan'))
    .description(
      `${$t('subscription.confirm_downgrade_free_plan')}\n\n${$t(
        'subscription.features_access_lost'
      )}`
    )
    .btnCancelName($t('general.dismiss'))
    .btnConfirmName($t('subscription.downgrade'))
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.post(
        route(`${routePrefix}.workspace.subscription.downgradeToFreePlan`, {
          workspace: activeWorkspaceId.value
        }),
        {},
        {
          preserveState: true,
          onSuccess() {
            dialog.reset()
          },
          onFinish() {
            dialog.isLoading(false)
          },
          onError
        }
      )
    })
    .show()
}

const resumeSubscription = () => {
  confirmation()
    .title($t('subscription.resume'))
    .description($t('subscription.confirm_resume'))
    .btnCancelName($t('general.dismiss'))
    .btnConfirmName($t('subscription.resume'))
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.post(
        route(`${routePrefix}.workspace.subscription.resume`, {
          workspace: activeWorkspaceId.value
        }),
        {},
        {
          preserveState: true,
          onSuccess() {
            dialog.reset()
          },
          onFinish() {
            dialog.isLoading(false)
          },
          onError
        }
      )
    })
    .show()
}

const redirectToPortal = () => {
  router.put(
    route(`${routePrefix}.workspace.portalPaymentPlatform`, { workspace: activeWorkspaceId.value })
  )
}

onMounted(() => {
  if (props.hasDelay) {
    setTimeout(() => {
      const currentUrl = window.location.href
      window.location.href = currentUrl.split('?')[0]
    }, 7000)
  }
})
</script>
<template>
  <Head :title="$t('general.billing')" />

  <div class="w-full">
    <PageHeader :title="$t('general.billing')" :with-padding-x="false" />

    <Preloader v-if="hasDelay">
      {{ $t('dashboard.processing') }}
    </Preloader>

    <Panel>
      <template #title>
        {{ $t('subscription.current_sub_plan') }}
      </template>

      <template v-if="subscription">
        <SubscriptionPlan :subscription="subscription" :currency="currency" />
        <SubscriptionStatusBadge :status="subscription.status" class="mt-xs" />
        <SubscriptionEnds :subscription="subscription" />

        <Flex class="mt-lg">
          <template v-if="$page.props.can_be_resumed">
            <PrimaryButton @click="resumeSubscription">
              {{ $t('subscription.resume') }}
            </PrimaryButton>
          </template>

          <template v-else>
            <Link :href="route(`${routePrefix}.workspace.upgrade`, { workspace: workspace.uuid })">
              <PrimaryButton>
                <span v-if="isCanceled(subscription.status)">{{ $t('subscription.resume') }}</span>
                <span v-else>{{ $t('subscription.change_sub_plan') }}</span>
              </PrimaryButton>
            </Link>
          </template>
        </Flex>
      </template>

      <template v-if="workspace.generic_subscription">
        <p class="text-lg font-semibold">{{ workspace.generic_subscription.name }}</p>

        <template
          v-if="
            !workspace.generic_subscription.free && workspace.generic_subscription.trial_ends_at
          "
        >
          <SubscriptionStatusBadge status="trialing" />
          <SubscriptionEnds
            :subscription="{
              status: 'trialing',
              trial_ends_at: workspace.generic_subscription.trial_ends_at
            }"
          />
        </template>
      </template>

      <template v-if="!subscription && !workspace.generic_subscription">
        <p>{{ $t('subscription.no_active_sub') }}</p>
      </template>

      <template v-if="!subscription">
        <div class="mt-lg">
          <Link :href="route(`${routePrefix}.workspace.upgrade`, { workspace: workspace.uuid })">
            <PrimaryButton>
              {{ $t('general.subscribe') }}
            </PrimaryButton>
          </Link>
        </div>
      </template>

      <template
        v-if="
          freePlanExists &&
          (subscription || (workspace.generic_subscription && !workspace.generic_subscription.free))
        "
      >
        <Flex>
          <div class="mt-lg" @click="downgradeSubscriptionToFreePlan">
            <SecondaryButton>
              {{ $t('subscription.downgrade_free_plan') }}
            </SecondaryButton>
          </div>
        </Flex>
      </template>
    </Panel>

    <template v-if="subscription">
      <Panel class="mt-lg">
        <template #title>
          {{ $t('dashboard.payment_method') }}
        </template>

        <PaymentDetails :payment-method="workspace.payment_method" />

        <PrimaryButton
          :class="workspace.payment_method.type ? 'mt-lg' : ''"
          @click="redirectToPortal"
        >
          {{ $t('dashboard.update_payment_method') }}
        </PrimaryButton>
      </Panel>
    </template>

    <template v-if="subscription && canCancel(subscription.status)">
      <Panel class="mt-lg">
        <template #title>
          {{ $t('subscription.cancel_sub') }}
        </template>

        <template #description>
          {{ $t('subscription.can_cancel_sub') }}
        </template>

        <DangerButton class="btn-primary danger in-form" @click="cancelSubscription">
          {{ $t('subscription.cancel_sub') }}
        </DangerButton>
      </Panel>
    </template>
  </div>
</template>
