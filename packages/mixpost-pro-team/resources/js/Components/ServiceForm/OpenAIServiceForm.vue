<script setup>
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import useNotifications from '@/Composables/useNotifications'
import Panel from '@/Components/Surface/Panel.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import Error from '@/Components/Form/Error.vue'
import ReadDocHelp from '@/Components/Util/ReadDocHelp.vue'
import InputHidden from '../Form/InputHidden.vue'
import OpenAI from '../../Icons/OpenAI.vue'
import Checkbox from '../Form/Checkbox.vue'
import Flex from '../Layout/Flex.vue'
import Label from '../Form/Label.vue'
import LabelSuffix from '../Form/LabelSuffix.vue'
import HorizontalGroup from '../Layout/HorizontalGroup.vue'

const { t: $t } = useI18n()

const props = defineProps({
  form: {
    required: true,
    type: Object
  }
})

const { notify } = useNotifications()
const errors = ref({})

const save = () => {
  errors.value = {}

  router.put(route('mixpost.services.update', { service: 'openai' }), props.form, {
    preserveScroll: true,
    onSuccess() {
      notify('success', $t('service.service_saved', { service: 'Open AI' }))
    },
    onError: err => {
      errors.value = err
    }
  })
}
</script>
<template>
  <Panel class="mt-lg">
    <template #title>
      <div class="flex items-center">
        <span class="mr-xs"><OpenAI class="text-openai" /></span>
        <span>OpenAI</span>
      </div>
    </template>

    <template #description>
      <p>
        <a href="https://platform.openai.com/account/api-keys" class="link" target="_blank"
          >You can generate an API key here</a
        >.
      </p>
      <ReadDocHelp :href="`${$page.props.mixpost.docs_link}/services/ai/open-ai`" class="mt-xs" />
    </template>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="secret">API Key <LabelSuffix danger>*</LabelSuffix></Label>
      </template>

      <InputHidden
        id="secret"
        v-model="form.configuration.secret_key"
        :error="errors['configuration.secret_key'] !== undefined"
        placeholder="sk-..."
        autocomplete="new-password"
      />

      <template #footer>
        <Error :message="errors['configuration.secret_key']" />
      </template>
    </HorizontalGroup>

    <HorizontalGroup class="mt-lg">
      <template #title>
        {{ $t('general.status') }}
      </template>

      <Flex :responsive="false" class="items-center">
        <Checkbox id="active" v-model:checked="form.active" />
        <Label for="active" class="mb-0!">{{ $t('general.active') }}</Label>
      </Flex>

      <template #footer>
        <Error :message="errors.active" />
      </template>
    </HorizontalGroup>

    <PrimaryButton class="mt-lg" @click="save">{{ $t('general.save') }}</PrimaryButton>
  </Panel>
</template>
