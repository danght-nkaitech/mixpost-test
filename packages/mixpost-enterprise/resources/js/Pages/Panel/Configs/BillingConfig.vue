<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import useNotifications from '../../../Composables/useNotifications'
import { cloneDeep } from 'lodash'
import Settings from '../../../Layouts/Child/Settings.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import Select from '../../../Components/Form/Select.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Input from '../../../Components/Form/Input.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import Label from '../../../Components/Form/Label.vue'
import Radio from '../../../Components/Form/Radio.vue'
import SectionBorder from '../../../Components/Surface/SectionBorder.vue'
import Textarea from '../../../Components/Form/Textarea.vue'
import InputLength from '../../../Components/Form/InputLength.vue'
import { convertLaravelErrorsToString } from '@/helpers.js'

const props = defineProps({
  configs: {
    type: Object
  },
  currencies: {
    required: true,
    type: Array
  }
})

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const form = useForm(cloneDeep(props.configs))

const { notify } = useNotifications()

const save = () => {
  form.put(route(`${routePrefix}.configs.billing.update`), {
    preserveScroll: true,
    onSuccess: () => {
      notify('success', $t('panel.billing_config_saved'))
    },
    onError: error => {
      notify('error', convertLaravelErrorsToString(error))
    }
  })
}
</script>
<template>
  <Head :title="$t('panel.billing_settings')" />

  <Settings>
    <form @submit.prevent="save">
      <Panel>
        <template #title>{{ $t('general.billing') }}</template>
        <template #description>{{ $t('panel.billing_settings_subscriptions') }}</template>

        <HorizontalGroup>
          <template #title>{{ $t('finance.currency') }}</template>

          <v-select
            v-model="form.currency"
            :reduce="option => option.key"
            :options="currencies"
            :close-on-select="true"
            :clearable="false"
            :searchable="true"
            class="w-full"
          >
          </v-select>
        </HorizontalGroup>

        <HorizontalGroup class="mt-lg">
          <template #title>{{ $t('subscription.billing_cycle') }}</template>
          <Select v-model="form.billing_cycle">
            <option value="monthly_yearly">{{ $t('panel.monthly_yearly') }}</option>
            <option value="monthly">{{ $t('panel.monthly_only') }}</option>
            <option value="yearly">{{ $t('panel.yearly_only') }}</option>
          </Select>
        </HorizontalGroup>

        <SectionBorder />

        <HorizontalGroup>
          <template #title>{{ $t('panel.trial_days') }}</template>
          <template #description>{{ $t('panel.number_days_trial') }}</template>
          <InputLength v-model="form.trial_days" :min="0" :max-limit="3" />
        </HorizontalGroup>

        <HorizontalGroup v-if="form.trial_days && parseInt(form.trial_days) !== 0" class="mt-lg">
          <template #title>{{ $t('panel.generic_trial') }}</template>
          <template #description>{{ $t('panel.trial_offer') }}</template>
          <Flex>
            <Label>
              <Radio v-model:checked="form.generic_trial" :value="true" />
              {{ $t('general.yes') }}
            </Label>

            <Label>
              <Radio v-model:checked="form.generic_trial" :value="false" />
              {{ $t('general.no') }}
            </Label>
          </Flex>
        </HorizontalGroup>

        <SectionBorder />

        <HorizontalGroup>
          <template #title>{{ $t('finance.prorate') }}</template>
          <template #description>{{ $t('finance.prorate_charges') }}</template>
          <Flex>
            <Label>
              <Radio v-model:checked="form.prorate" :value="true" />
              {{ $t('general.yes') }}
            </Label>

            <Label>
              <Radio v-model:checked="form.prorate" :value="false" />
              {{ $t('general.no') }}
            </Label>
          </Flex>
        </HorizontalGroup>

        <HorizontalGroup class="mt-lg">
          <template #title>{{ $t('finance.swap_invoice') }}</template>
          <template #description>{{ $t('finance.swap_plans_invoice_user') }} </template>
          <Flex>
            <Label>
              <Radio v-model:checked="form.bill_immediately" :value="true" />
              {{ $t('general.yes') }}
            </Label>

            <Label>
              <Radio v-model:checked="form.bill_immediately" :value="false" />
              {{ $t('general.no') }}
            </Label>
          </Flex>
        </HorizontalGroup>

        <SectionBorder />

        <HorizontalGroup>
          <template #title>{{ $t('finance.receipt_title') }}</template>
          <Input v-model="form.receipt_title" type="text" :placeholder="$t('finance.receipt')" />
        </HorizontalGroup>

        <HorizontalGroup class="mt-lg">
          <template #title>{{ $t('panel.company_details') }}</template>
          <Textarea v-model="form.company_details" />
        </HorizontalGroup>

        <SectionBorder />

        <HorizontalGroup class="mt-lg">
          <template #title>{{ $t('plan.plans_page_url') }}</template>
          <Input
            v-model="form.plans_page_url"
            type="text"
            placeholder="https://your-domain.com/pricing"
          />
        </HorizontalGroup>

        <HorizontalGroup class="mt-lg">
          <template #title>{{ $t('plan.plans_page_url_title') }}</template>
          <Input
            v-model="form.plans_page_url_title"
            type="text"
            :placeholder="$t('general.learn_more')"
          />
        </HorizontalGroup>

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
