<script setup>
import { inject, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useForm } from '@inertiajs/vue3'
import useNotifications from '../../Composables/useNotifications'
import TableRow from '../DataDisplay/TableRow.vue'
import TableCell from '../DataDisplay/TableCell.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import DialogModal from '../Modal/DialogModal.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import Paddle from './Paddle.vue'
import PaddleBilling from './PaddleBilling.vue'
import Stripe from './Stripe.vue'
import Paystack from './Paystack.vue'
import HorizontalGroup from '../Layout/HorizontalGroup.vue'
import Radio from '../Form/Radio.vue'
import Error from '../Form/Error.vue'
import Badge from '../DataDisplay/Badge.vue'
import { convertLaravelErrorsToString } from '@/helpers.js'

const props = defineProps({
  platform: {
    required: true,
    type: Object
  }
})

const { t: $t } = useI18n()

const components = {
  Paddle,
  PaddleBilling,
  Stripe,
  Paystack
}

const routePrefix = inject('routePrefix')
const { notify } = useNotifications()

const opened = ref(false)

const form = useForm({
  credentials: props.platform.credentials,
  options: props.platform.options,
  enabled: props.platform.enabled
})

const open = () => {
  opened.value = true
}

const close = () => {
  opened.value = false
  form.reset()
  form.clearErrors()
}

watch(
  () => props.platform,
  value => {
    form.defaults({
      credentials: value.credentials,
      options: value.options,
      enabled: value.enabled
    })

    form.reset()
  }
)

const update = () => {
  form
    .transform(data => ({
      ...{ name: props.platform.name },
      ...data
    }))
    .put(route(`${routePrefix}.payment-platforms.update`), {
      preserveScroll: true,
      onSuccess() {
        notify('success', $t('panel.pay_plat_saved', { platform: props.platform.readable_name }))
      },
      onError: error => {
        notify('error', convertLaravelErrorsToString(error))
      }
    })
}
</script>
<template>
  <TableRow :hoverable="true">
    <TableCell :clickable="true" align="left" class="py-md!" @click="open">
      <span class="font-medium">{{ platform.readable_name }}</span>
    </TableCell>

    <TableCell :clickable="true" align="right" class="py-md!" @click="open">
      <Badge v-if="platform.enabled" variant="success">{{ $t('general.enabled') }}</Badge>
    </TableCell>
  </TableRow>

  <DialogModal :show="opened" max-width="lg" @close="close">
    <template #header>
      <span>
        <span class="font-medium">{{ platform.readable_name }}</span> -
        {{ $t('panel.pay_plat_config') }}</span
      >
    </template>

    <template #body>
      <template v-if="opened">
        <HorizontalGroup class="mt-xs">
          <div class="flex items-center space-x-sm">
            <label>
              <Radio v-model:checked="form.enabled" :value="true" />
              {{ $t('general.enabled') }}</label
            >
            <label>
              <Radio v-model:checked="form.enabled" :value="false" />
              {{ $t('general.disabled') }}</label
            >
          </div>

          <template #footer>
            <Error :message="form.errors.enabled" />
          </template>
        </HorizontalGroup>

        <div class="mt-lg">
          <Error
            v-for="(error, key) in form.errors"
            :key="key"
            :message="error"
            class="mb-xs last:mb-0"
          />

          <component :is="components[platform.component]" :form="form" />
        </div>
      </template>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="close">{{ $t('general.cancel') }}</SecondaryButton>
      <PrimaryButton :disabled="form.processing" :is-loading="form.processing" @click="update">{{
        $t('general.save')
      }}</PrimaryButton>
    </template>
  </DialogModal>
</template>
