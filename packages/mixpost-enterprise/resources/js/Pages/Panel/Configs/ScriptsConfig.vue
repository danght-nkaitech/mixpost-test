<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import useNotifications from '../../../Composables/useNotifications'
import { cloneDeep } from 'lodash'
import Settings from '../../../Layouts/Child/Settings.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Textarea from '../../../Components/Form/Textarea.vue'
import VerticalGroup from '../../../Components/Layout/VerticalGroup.vue'
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
  form.put(route(`${routePrefix}.configs.scripts.update`), {
    preserveScroll: true,
    onSuccess: () => {
      notify('success', $t('panel.scripts_config_saved'))
    },
    onError: error => {
      notify('error', convertLaravelErrorsToString(error))
    }
  })
}
</script>
<template>
  <Head :title="$t('panel.scripts_settings')" />

  <Settings>
    <form @submit.prevent="save">
      <Panel>
        <template #title>{{ $t('panel.scripts') }}</template>
        <template #description>{{ $t('panel.scripts_chats') }}</template>

        <VerticalGroup>
          <template #title>{{ $t('panel.scripts_inside', { tag: '&lt;head&gt;' }) }}</template>
          <Textarea v-model="form.head" />
        </VerticalGroup>

        <VerticalGroup class="mt-lg">
          <template #title>{{ $t('panel.scripts_inside', { tag: '&lt;body&gt;' }) }}</template>
          <Textarea v-model="form.body" />
        </VerticalGroup>

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
