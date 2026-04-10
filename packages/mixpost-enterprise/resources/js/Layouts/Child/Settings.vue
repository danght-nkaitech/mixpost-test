<script setup>
import { inject } from 'vue'
import { Link } from '@inertiajs/vue3'
import PageHeader from '../../Components/DataDisplay/PageHeader.vue'
import Tabs from '../../Components/Navigation/Tabs.vue'
import Tab from '../../Components/Navigation/Tab.vue'

const routePrefix = inject('routePrefix')
</script>
<template>
  <div class="row-py w-full mx-auto">
    <PageHeader :title="$t('general.settings')" />

    <div class="row-px">
      <Tabs class="overflow-x-auto flex-nowrap! md:flex-wrap! md:gap-sm max-w-full w-full mb-lg">
        <Link :href="route(`${routePrefix}.configs.system.view`)" class="mr-md">
          <Tab :active="$page.component === 'Panel/Configs/SystemConfig'">{{
            $t('panel.system')
          }}</Tab>
        </Link>

        <Link :href="route(`${routePrefix}.configs.billing.view`)" class="mr-md">
          <Tab :active="$page.component === 'Panel/Configs/BillingConfig'">{{
            $t('general.billing')
          }}</Tab>
        </Link>

        <Link :href="route(`${routePrefix}.payment-platforms.index`)" class="mr-md">
          <Tab :active="$page.component === 'Panel/PaymentPlatforms/PaymentPlatforms'">
            {{ $t('panel.pay_plats') }}
          </Tab>
        </Link>

        <Link :href="route(`${routePrefix}.configs.onboarding.view`)" class="mr-md">
          <Tab :active="$page.component === 'Panel/Configs/OnboardingConfig'">
            {{ $t('panel.onboarding') }}
          </Tab>
        </Link>

        <Link :href="route(`${routePrefix}.configs.theme.view`)" class="mr-md">
          <Tab :active="$page.component === 'Panel/Configs/ThemeConfig'">
            {{ $t('panel.customization') }}
          </Tab>
        </Link>

        <Link :href="route(`${routePrefix}.configs.scripts.view`)">
          <Tab :active="$page.component === 'Panel/Configs/ScriptsConfig'">{{
            $t('panel.scripts')
          }}</Tab>
        </Link>
      </Tabs>

      <slot />
    </div>
  </div>
</template>
