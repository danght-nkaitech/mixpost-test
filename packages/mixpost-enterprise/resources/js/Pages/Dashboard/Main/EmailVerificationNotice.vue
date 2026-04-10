<script setup>
import { inject } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import MinimalLayout from '@/Layouts/Minimal.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'

defineOptions({ layout: MinimalLayout })

const routePrefix = inject('routePrefix')
</script>
<template>
  <Head :title="$t('onboarding.verify_email')" />

  <div class="w-full">
    <Panel>
      <template #title>{{ $t('onboarding.verify_email') }}</template>
      <template #description>{{ $t('onboarding.confirm_email') }}</template>

      <Link :href="route(`${routePrefix}.emailVerification.resend`)" method="post" as="button">
        <PrimaryButton>{{ $t('onboarding.resend_verification') }}</PrimaryButton>
      </Link>
    </Panel>
  </div>
</template>
