<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import useNotifications from '../../../Composables/useNotifications'
import { cloneDeep } from 'lodash'
import Settings from '../../../Layouts/Child/Settings.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Radio from '../../../Components/Form/Radio.vue'
import SectionBorder from '../../../Components/Surface/SectionBorder.vue'
import Input from '../../../Components/Form/Input.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import { convertLaravelErrorsToString } from '@/helpers.js'

const props = defineProps({
  configs: {
    type: Object
  }
})

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const form = useForm(cloneDeep(props.configs))

const { notify } = useNotifications()

const save = () => {
  form.put(route(`${routePrefix}.configs.system.update`), {
    preserveScroll: true,
    onSuccess: () => {
      notify('success', $t('panel.system_config_saved'))
    },
    onError: error => {
      notify('error', convertLaravelErrorsToString(error))
    }
  })
}
</script>
<template>
  <Head :title="$t('panel.system_settings_title')" />

  <Settings>
    <form @submit.prevent="save">
      <Panel>
        <template #title>{{ $t('panel.system') }}</template>
        <template #description>{{ $t('panel.system_settings') }}</template>

        <HorizontalGroup class="mt-lg">
          <template #title>{{ $t('user.users_create_workspaces') }}</template>
          <Flex>
            <label>
              <Radio v-model:checked="form.multiple_workspaces" :value="true" />
              {{ $t('general.yes') }}</label
            >
            <label>
              <Radio v-model:checked="form.multiple_workspaces" :value="false" />
              {{ $t('general.no') }}</label
            >
          </Flex>
        </HorizontalGroup>

        <SectionBorder />

        <HorizontalGroup>
          <template #title>{{ $t('panel.twitter_api_workspace') }}</template>
          <Flex>
            <label>
              <Radio v-model:checked="form.workspace_twitter_service" :value="true" />
              {{ $t('general.yes') }}</label
            >
            <label>
              <Radio v-model:checked="form.workspace_twitter_service" :value="false" />
              {{ $t('general.no') }}</label
            >
          </Flex>
        </HorizontalGroup>

        <template v-if="form.workspace_twitter_service">
          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="twitter_api_workspace_docs_url">{{ $t('panel.docs_url') }}</label>
            </template>
            <template #description>
              {{ $t('panel.twitter_api_workspace_docs_tip') }}
            </template>
            <Input
              id="twitter_api_workspace_docs_url"
              v-model="form.twitter_api_workspace_docs_url"
              type="text"
              :placeholder="$t('panel.docs_url')"
            />
          </HorizontalGroup>
        </template>

        <PrimaryButton
          :disabled="form.processing"
          :is-loading="form.processing"
          type="submit"
          class="mt-lg"
        >
          {{ $t('general.save') }}
        </PrimaryButton>
      </Panel>
    </form>
  </Settings>
</template>
