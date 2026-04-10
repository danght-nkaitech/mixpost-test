<script setup>
import { inject } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import {
  ACCESS_STATUS_SUBSCRIPTION,
  ACCESS_STATUS_UNLIMITED,
  ACCESS_STATUS_LOCKED
} from '@/Constants/Workspace.js'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import WorkspaceUsers from '../../../Components/Workspace/WorkspaceUsers.vue'
import UserLink from '../../../Components/User/UserLink.vue'
import WorkspaceSubscription from '../../../Components/Workspace/WorkspaceSubscription.vue'
import Indicators from '../../../Components/Workspace/Indicators.vue'
import Actions from '../../../Components/Workspace/Actions.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import PaymentDetails from '../../../Components/Subscription/PaymentDetails.vue'
import ClipboardCard from '../../../Components/Util/ClipboardCard.vue'

const routePrefix = inject('routePrefix')

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

const redirectToPortal = () => {
  router.put(
    route(`${routePrefix}.workspaces.portalPaymentPlatform`, { workspace: props.workspace.uuid })
  )
}
</script>
<template>
  <Head :title="$t('workspace.view_workspace')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('workspace.view_workspace')">
      <template #description>
        {{ workspace.name }}
      </template>

      <Actions :workspace="workspace" :view="false" />
    </PageHeader>

    <div class="row-px">
      <Panel>
        <template #title>{{ $t('general.details') }}</template>

        <div class="md:max-w-(--container-2xl)">
          <Indicators :workspace="workspace" conditional-class="mb-lg" />

          <HorizontalGroup>
            <template #title>
              {{ $t('general.name') }}
            </template>

            <div>{{ workspace.name }}</div>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('general.owner') }}
            </template>

            <template v-if="workspace.owner">
              <UserLink :id="workspace.owner.id" :name="workspace.owner.name" />
            </template>
            <template v-else> - </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('general.access_status') }}
            </template>

            <div v-if="workspace.access_status === ACCESS_STATUS_SUBSCRIPTION">
              {{ $t('subscription.requires_subscription') }}
            </div>
            <div v-if="workspace.access_status === ACCESS_STATUS_UNLIMITED">
              {{ $t('workspace.unlimited') }}
            </div>
            <div v-if="workspace.access_status === ACCESS_STATUS_LOCKED">
              {{ $t('workspace.locked') }}
            </div>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('theme.color') }}
            </template>

            <div :style="{ background: workspace.hex_color }" class="w-xl h-xl rounded-md" />
          </HorizontalGroup>
        </div>
      </Panel>

      <div class="mt-lg">
        <WorkspaceSubscription
          :workspace="workspace"
          :subscription="subscription"
          :currency="currency"
          :billing-configs="billingConfigs"
          :plans="plans"
        />
      </div>

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

      <div class="mt-lg">
        <WorkspaceUsers :workspace="workspace" />
      </div>

      <template v-if="$page.props.mixpost.features.api_access_tokens">
        <Panel class="mt-lg">
          <template #title>
            {{ $t('system.usage_api') }}
          </template>

          <ClipboardCard>{{ workspace.uuid }}</ClipboardCard>
        </Panel>
      </template>
    </div>
  </div>
</template>
