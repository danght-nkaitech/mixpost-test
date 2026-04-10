<script setup>
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import NProgress from 'nprogress'
import useNotifications from '@/Composables/useNotifications'
import Panel from '@/Components/Surface/Panel.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import Error from '@/Components/Form/Error.vue'
import ReadDocHelp from '@/Components/Util/ReadDocHelp.vue'
import InputHidden from '../Form/InputHidden.vue'
import Checkbox from '../Form/Checkbox.vue'
import Flex from '../Layout/Flex.vue'
import Label from '../Form/Label.vue'
import LabelSuffix from '../Form/LabelSuffix.vue'
import HorizontalGroup from '../Layout/HorizontalGroup.vue'
import ProviderIcon from '../Account/ProviderIcon.vue'
import PureButton from '../Button/PureButton.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const props = defineProps({
  form: {
    required: true,
    type: Object
  }
})

const { notify } = useNotifications()
const errors = ref({})

const newPrivateKey = async () => {
  if (!props.form.configuration.private_key) {
    await generatePrivateKey()
    return
  }

  confirmation()
    .title('Generate new private key')
    .description($t('page.are_you_sure'))
    .onConfirm(async dialog => {
      dialog.isLoading(true)

      generatePrivateKey().then(() => {
        dialog.isLoading(false)
        dialog.close()
      })
    })
    .show()
}
const generatePrivateKey = () => {
  return new Promise((resolve, reject) => {
    NProgress.start()
    axios
      .post(route(`${routePrefix}.services.generateBlueskyPrivateKey`))
      .then(response => {
        props.form.configuration.private_key = response.data.private_key

        notify(
          'success',
          'New private key generated.<br/> Please save the form to apply the changes.'
        )

        resolve()
      })
      .catch(reject)
      .finally(() => NProgress.done())
  })
}

const save = () => {
  errors.value = {}

  router.put(route(`${routePrefix}.services.update`, { service: 'bluesky' }), props.form, {
    preserveScroll: true,
    onSuccess() {
      notify('success', $t('service.service_saved', { service: 'Bluesky' }))
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
        <span class="mr-xs">
          <ProviderIcon provider="bluesky" />
        </span>
        <span>Bluesky</span>
      </div>
    </template>

    <template #description>
      <ReadDocHelp :href="`${$page.props.mixpost.docs_link}/services/social/bluesky/`" />
    </template>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="private_key"
          >Private Key
          <LabelSuffix danger>*</LabelSuffix>
        </Label>
      </template>

      <div>
        <InputHidden
          id="private_key"
          v-model="form.configuration.private_key"
          :error="errors['configuration.private_key'] !== undefined"
          :disabled="true"
          class="mb-xs"
          autocomplete="off"
        />
        <PureButton :link-style="true" @click="newPrivateKey">Generate new private key</PureButton>
      </div>

      <template #footer>
        <Error :message="errors['configuration.private_key']" />
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
