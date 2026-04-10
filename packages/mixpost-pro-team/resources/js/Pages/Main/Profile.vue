<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import MinimalLayout from '@/Layouts/Minimal.vue'
import Preferences from '../../Components/Profile/Preferences.vue'
import UserAccount from '../../Components/Profile/UserAccount.vue'
import TwoFactorAuth from '../../Components/Profile/TwoFactorAuth.vue'
import Password from '../../Components/Profile/Password.vue'
import ActionSection from '../../Components/Surface/ActionSection.vue'
import SectionBorder from '../../Components/Surface/SectionBorder.vue'
import PageHeader from '../../Components/DataDisplay/PageHeader.vue'
import DeleteAccount from '../../Components/Profile/DeleteAccount.vue'
import BackToDashboardButton from '../../Components/Helper/BackToDashboardButton.vue'

defineOptions({ layout: MinimalLayout })

const props = defineProps({
  settings: {
    type: Object,
    required: true
  },
  locales: {
    type: Array,
    required: true
  },
  timezoneList: {
    type: Object,
    required: true
  },
  userHasTwoFactorAuthEnabled: {
    type: Boolean,
    required: true
  },
  isDeleteAccountEnabled: {
    type: Boolean,
    required: true
  }
})

const form = useForm(props.settings)
</script>
<template>
  <Head :title="$t('profile.edit_profile')" />

  <PageHeader :title="$t('profile.edit_profile')" class="px-0!">
    <BackToDashboardButton />
  </PageHeader>

  <div class="row-py w-full mx-auto pt-0!">
    <div class="w-full mb-lg">
      <div class="mt-2xl sm:mt-0">
        <ActionSection>
          <template #title>{{ $t('profile.profile_information') }}</template>
          <template #description>{{ $t('profile.update_your_account') }}</template>

          <UserAccount />
        </ActionSection>
      </div>

      <SectionBorder :contrasted="true" />

      <div class="mt-2xl sm:mt-0">
        <ActionSection>
          <template #title>{{ $t('profile.preferences') }}</template>
          <template #description>{{ $t('profile.update_preferences') }}</template>

          <Preferences :form="form" :timezone-list="timezoneList" :locales="locales" />
        </ActionSection>
      </div>

      <template v-if="$page.props.is_two_factor_auth_enabled">
        <SectionBorder :contrasted="true" />

        <div class="mt-2xl sm:mt-0">
          <ActionSection>
            <template #title>{{ $t('auth.two_factor_authentication') }}</template>
            <template #description>{{ $t('auth.security_using_two_factor') }} </template>

            <TwoFactorAuth />
          </ActionSection>
        </div>
      </template>

      <SectionBorder :contrasted="true" />

      <div class="mt-2xl sm:mt-0">
        <ActionSection>
          <template #title>{{ $t('auth.update_password') }}</template>
          <template #description>{{ $t('auth.confirm_secure_password') }} </template>

          <Password />
        </ActionSection>
      </div>

      <template v-if="isDeleteAccountEnabled">
        <SectionBorder :contrasted="true" />

        <div class="mt-2xl sm:mt-0">
          <ActionSection>
            <template #title>{{ $t('profile.delete_account') }}</template>
            <template #description>{{ $t('profile.delete_account_desc') }}</template>

            <DeleteAccount />
          </ActionSection>
        </div>
      </template>
    </div>
  </div>
</template>
