<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import { cloneDeep } from 'lodash'
import usePageMode from '@/Composables/usePageMode'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Error from '@/Components/Form/Error.vue'
import Input from '@/Components/Form/Input.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import Textarea from '../../../Components/Form/Textarea.vue'
import LabelSuffix from '../../../Components/Form/LabelSuffix.vue'
import ReceiptAction from '../../../Components/Receipt/ReceiptAction.vue'
import SelectWorkspace from '../../../Components/Workspace/SelectWorkspace.vue'

const props = defineProps({
  mode: {
    type: String,
    default: 'create'
  },
  defaultCurrency: {
    required: true,
    type: String
  },
  currencies: {
    required: true,
    type: Array
  },
  receipt: {
    type: Object
  }
})

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const pageTitle = computed(() => {
  if (isCreate.value) {
    return $t('finance.create_receipt')
  }

  return $t('finance.edit_receipt')
})

const { isCreate, isEdit } = usePageMode()

const selectedWorkspace = ref(
  isEdit.value && props.receipt.workspace
    ? {
        key: props.receipt.workspace.uuid,
        label: props.receipt.workspace.name
      }
    : null
)

const form = useForm(
  isEdit.value
    ? cloneDeep(props.receipt)
    : {
        workspace_uuid: null,
        transaction_id: '',
        invoice_number: '',
        amount: 0,
        tax: 0,
        currency: props.defaultCurrency,
        description: '',
        paid_at: '',
        paid_at_raw: ''
      }
)

const getTransformedForm = () => {
  return form.transform(data => ({
    ...data,
    ...{
      workspace_uuid: selectedWorkspace.value ? selectedWorkspace.value.key : null,
      paid_at: form.paid_at_raw
    }
  }))
}

const store = () => {
  getTransformedForm().post(route(`${routePrefix}.receipts.store`))
}

const update = () => {
  getTransformedForm().put(route(`${routePrefix}.receipts.update`, { receipt: props.receipt.uuid }))
}

const submit = () => {
  if (isCreate.value) {
    store()
  }

  if (isEdit.value) {
    update()
  }
}
</script>
<template>
  <Head :title="pageTitle" />

  <div class="row-py w-full mx-auto">
    <PageHeader :title="pageTitle" :flex-wrap="true">
      <template #description>
        {{ isEdit ? receipt.order_id : '' }}
      </template>

      <template v-if="isEdit">
        <ReceiptAction :receipt="receipt" :edit="false" />
      </template>
    </PageHeader>

    <div class="row-px">
      <form method="post" @submit.prevent="submit">
        <Panel>
          <template #title>{{ $t('general.details') }}</template>

          <HorizontalGroup>
            <template #title>
              <label
                >{{ $t('workspace.workspace') }}
                <LabelSuffix danger>*</LabelSuffix>
              </label>
            </template>

            <SelectWorkspace v-model="selectedWorkspace" :disabled="isEdit" />

            <template #footer>
              <Error :message="form.errors.workspace_uuid" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="invoice_number"
                >{{ $t('finance.invoice_number') }}
                <LabelSuffix danger>*</LabelSuffix>
              </label>
            </template>

            <Input
              id="invoice_number"
              v-model="form.invoice_number"
              type="text"
              class="w-full"
              autocomplete="off"
              :autofocus="isCreate"
              required
            />

            <template #footer>
              <Error :message="form.errors.invoice_number" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="transaction_id"
                >{{ $t('finance.transaction_id') }}
                <LabelSuffix danger>*</LabelSuffix>
              </label>
            </template>

            <Input
              id="transaction_id"
              v-model="form.transaction_id"
              type="text"
              class="w-full"
              autocomplete="off"
              required
            />

            <template #footer>
              <Error :message="form.errors.transaction_id" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="amount"
                >{{ $t('finance.amount') }}
                <LabelSuffix danger>*</LabelSuffix>
              </label>
            </template>

            <Input id="amount" v-model="form.amount" type="text" class="w-full" required />

            <template #footer>
              <Error :message="form.errors.amount" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="tax"
                >{{ $t('finance.tax') }}
                <LabelSuffix danger>*</LabelSuffix>
              </label>
            </template>

            <Input id="tax" v-model="form.tax" type="text" class="w-full" required />

            <template #footer>
              <Error :message="form.errors.tax" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
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

            <template #footer>
              <Error :message="form.errors.currency" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="description"
                >{{ $t('general.description') }}
                <LabelSuffix danger>*</LabelSuffix>
              </label>
            </template>

            <Textarea id="description" v-model="form.description" class="w-full" required />

            <template #footer>
              <Error :message="form.errors.description" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="paid_at"
                >{{ $t('finance.paid_at') }}
                <LabelSuffix danger>*</LabelSuffix>
              </label>
            </template>

            <Input id="paid_at" v-model="form.paid_at_raw" type="datetime-local" class="w-full" />

            <template #footer>
              <Error :message="form.errors.paid_at" />
            </template>
          </HorizontalGroup>

          <div class="mt-lg">
            <PrimaryButton type="submit" :disabled="form.processing" :is-loading="form.processing">
              {{ isCreate ? $t('general.create') : $t('workspace.update') }}
            </PrimaryButton>
          </div>
        </Panel>
      </form>
    </div>
  </div>
</template>
