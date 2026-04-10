<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import useNotifications from '@/Composables/useNotifications'
import Panel from '@/Components/Surface/Panel.vue'
import Input from '@/Components/Form/Input.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import Error from '@/Components/Form/Error.vue'
import ReadDocHelp from '@/Components/Util/ReadDocHelp.vue'
import Checkbox from '../Form/Checkbox.vue'
import Flex from '../Layout/Flex.vue'
import Label from '../Form/Label.vue'
import LabelSuffix from '../Form/LabelSuffix.vue'
import HorizontalGroup from '../Layout/HorizontalGroup.vue'
import Shlink from '@/Icons/Shlink.vue'

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

  router.put(route('mixpost.services.update', { service: 'shlink' }), props.form, {
    preserveScroll: true,
    onSuccess() {
      notify('success', $t('service.service_saved', { service: 'Shlink' }))
    },
    onError: err => {
      errors.value = err
    }
  })
}
</script>
<template>
  <Panel>
    <template #title>
      <div class="flex items-center">
        <span class="mr-xs"><Shlink /></span>
        <span>Shlink</span>
      </div>
    </template>

    <template #description>
      Self-hosted URL shortener<br />
      <a href="https://shlink.io/documentation/install-dist-file/" class="link" target="_blank">
        Install Shlink</a
      >
      <ReadDocHelp
        :href="`${$page.props.mixpost.docs_link}/services/url-shortener/shlink`"
        class="mt-xs"
      />
    </template>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="domain_url">Shlink Domain <LabelSuffix danger>*</LabelSuffix></Label>
      </template>

      <Input
        id="domain_url"
        v-model="form.configuration.domain_url"
        :error="errors['configuration.domain_url'] !== undefined"
        autocomplete="new-password"
      />

      <template #footer>
        <Error :message="errors['configuration.domain_url']" />
      </template>
    </HorizontalGroup>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="api_key">API Key <LabelSuffix danger>*</LabelSuffix></Label>
      </template>

      <Input
        id="api_key"
        v-model="form.configuration.api_key"
        :error="errors['configuration.api_key'] !== undefined"
        autocomplete="new-password"
      />

      <template #footer>
        <Error :message="errors['configuration.api_key']" />
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
