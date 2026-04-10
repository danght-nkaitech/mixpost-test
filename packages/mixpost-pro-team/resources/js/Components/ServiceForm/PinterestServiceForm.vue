<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import useNotifications from '@/Composables/useNotifications'
import Panel from '@/Components/Surface/Panel.vue'
import Input from '@/Components/Form/Input.vue'
import PinterestIcon from '@/Icons/Pinterest.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Error from '@/Components/Form/Error.vue'
import ReadDocHelp from '@/Components/Util/ReadDocHelp.vue'
import Select from '../Form/Select.vue'
import InputHidden from '../Form/InputHidden.vue'
import Checkbox from '../Form/Checkbox.vue'
import Flex from '../Layout/Flex.vue'
import Label from '../Form/Label.vue'
import LabelSuffix from '../Form/LabelSuffix.vue'

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

  router.put(route('mixpost.services.update', { service: 'pinterest' }), props.form, {
    preserveScroll: true,
    onSuccess() {
      notify('success', $t('service.service_saved', { service: 'Pinterest' }))
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
        <span class="mr-xs"><PinterestIcon class="text-pinterest" /></span>
        <span>Pinterest</span>
      </div>
    </template>

    <template #description>
      <p>
        <a href="https://developers.pinterest.com/apps/" class="link" target="_blank">{{
          $t('service.create_app', { name: 'Pinterest' })
        }}</a
        >.
      </p>
      <ReadDocHelp
        :href="`${$page.props.mixpost.docs_link}/services/social/pinterest`"
        class="mt-xs"
      />
    </template>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="id">App ID <LabelSuffix danger>*</LabelSuffix></Label>
      </template>

      <Input
        id="id"
        v-model="form.configuration.client_id"
        :error="errors['configuration.client_id'] !== undefined"
        type="text"
        autocomplete="off"
      />

      <template #footer>
        <Error :message="errors['configuration.client_id']" />
      </template>
    </HorizontalGroup>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="secret">App secret key <LabelSuffix danger>*</LabelSuffix></Label>
      </template>

      <InputHidden
        id="secret"
        v-model="form.configuration.client_secret"
        :error="errors['configuration.client_secret'] !== undefined"
        autocomplete="new-password"
      />

      <template #footer>
        <Error :message="errors['configuration.client_secret']" />
      </template>
    </HorizontalGroup>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="env">Environment</Label>
      </template>

      <Select
        id="env"
        v-model="form.configuration.environment"
        :error="errors['configuration.environment'] !== undefined"
      >
        >
        <option value="sandbox">Sandbox</option>
        <option value="production">Production</option>
      </Select>

      <template #footer>
        <Error :message="errors['configuration.environment']" />
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
