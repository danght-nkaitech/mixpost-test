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
import Bitly from '@/Icons/Bitly.vue'

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

  router.put(route('mixpost.services.update', { service: 'bitly' }), props.form, {
    preserveScroll: true,
    onSuccess() {
      notify('success', $t('service.service_saved', { service: 'Bitly' }))
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
        <span class="mr-xs"><Bitly /></span>
        <span>Bitly</span>
      </div>
    </template>

    <template #description>
      URL shortener<br />
      <a href="https://bitly.com" class="link" target="_blank"> Register an account</a>
      <ReadDocHelp
        :href="`${$page.props.mixpost.docs_link}/services/url-shortener/bitly`"
        class="mt-xs"
      />
    </template>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="token">Token <LabelSuffix danger>*</LabelSuffix></Label>
      </template>

      <Input
        id="token"
        v-model="form.configuration.token"
        :error="errors['configuration.token'] !== undefined"
        autocomplete="new-password"
      />

      <template #footer>
        <Error :message="errors['configuration.token']" />
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
