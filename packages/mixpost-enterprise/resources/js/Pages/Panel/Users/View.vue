<script setup>
import { useI18n } from 'vue-i18n'
import { Head } from '@inertiajs/vue3'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import UserWorkspaces from '../../../Components/User/UserWorkspaces.vue'
import Indicators from '../../../Components/User/Indicators.vue'
import Actions from '../../../Components/User/Actions.vue'

const { t: $t } = useI18n()

defineProps({
  user: {
    type: Object
  },
  emailVerification: {
    type: Boolean
  }
})
</script>
<template>
  <Head :title="$t('user.view_user')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('user.view_user')">
      <template #description>
        {{ user.name }}
      </template>

      <Actions :user="user" :view="false" />
    </PageHeader>

    <div class="row-px">
      <Panel>
        <template #title>{{ $t('user.user_details') }}</template>

        <div class="form-field">
          <Indicators :user="user" conditional-class="mb-lg" />

          <HorizontalGroup>
            <template #title>
              {{ $t('general.name') }}
            </template>

            {{ user.name }}
          </HorizontalGroup>

          <HorizontalGroup class="mt-md">
            <template #title>
              {{ $t('general.email') }}
            </template>

            {{ user.email }}
          </HorizontalGroup>

          <HorizontalGroup class="mt-md">
            <template #title>
              {{ $t('general.created_at') }}
            </template>

            {{ user.created_at }}
          </HorizontalGroup>

          <template v-if="emailVerification">
            <HorizontalGroup class="mt-md">
              <template #title>
                {{ $t('user.email_verified_at') }}
              </template>

              {{ user.email_verified_at ? user.email_verified_at : '-' }}
            </HorizontalGroup>
          </template>
        </div>
      </Panel>

      <div class="mt-lg">
        <UserWorkspaces :user="user" />
      </div>
    </div>
  </div>
</template>
