<script setup>
import { computed, inject } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import useRouter from '../../../Composables/useRouter'
import AdminLayout from '@/Layouts/Admin.vue'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import usePageMode from '../../../Composables/usePageMode'
import WebhookAction from '../../../Components/Webhook/WebhookAction.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import Error from '../../../Components/Form/Error.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Input from '../../../Components/Form/Input.vue'
import Select from '../../../Components/Form/Select.vue'
import Checkbox from '../../../Components/Form/Checkbox.vue'
import LabelSuffix from '../../../Components/Form/LabelSuffix.vue'
import Label from '../../../Components/Form/Label.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import ChangeSecret from '../../../Components/Webhook/ChangeSecret.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  mode: {
    type: String,
    default: 'create'
  },
  record: {
    type: Object
  },
  events: {
    type: Object
  }
})

const routePrefix = inject('routePrefix')

const { isCreate, isEdit } = usePageMode()
const { onError } = useRouter()

const form = useForm({
  name: isEdit.value ? props.record.name : '',
  callback_url: isEdit.value ? props.record.callback_url : '',
  method: isEdit.value ? props.record.method : 'post',
  content_type: isEdit.value ? props.record.content_type : 'application/json',
  max_attempts: isEdit.value ? props.record.max_attempts : 1,
  secret: isEdit.value ? props.record.secret : '',
  events: isEdit.value ? props.record.events : [],
  active: isEdit.value ? props.record.active : true
})

const showContentType = computed(() => {
  return form.method === 'post' || form.method === 'put' || form.method === 'delete'
})

const store = (login = false) => {
  form
    .transform(data => ({
      ...data,
      login
    }))
    .post(route(`${routePrefix}.system.webhooks.store`), {
      onError: errors => {
        onError(errors, store)
      }
    })
}

const update = () => {
  form.put(route(`${routePrefix}.system.webhooks.update`, { webhook: props.record.id }), {
    preserveScroll: true,
    onError: errors => {
      onError(errors, update)
    }
  })
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
  <Head :title="mode === 'create' ? $t('webhook.create_webhook') : $t('webhook.edit_webhook')" />

  <div class="w-full mx-auto row-py">
    <PageHeader
      :title="mode === 'create' ? $t('webhook.create_webhook') : $t('webhook.edit_webhook')"
    >
      <template v-if="isEdit">
        <WebhookAction :record="record" :edit="false" />
      </template>
    </PageHeader>

    <div class="row-px">
      <form method="post" @submit.prevent="submit">
        <Panel>
          <template #title>{{ $t('general.details') }}</template>

          <HorizontalGroup class="form-field">
            <template #title>
              <label for="name"
                >{{ $t('general.name') }}
                <LabelSuffix :danger="true">*</LabelSuffix>
              </label>
            </template>

            <Input
              id="name"
              v-model="form.name"
              type="text"
              :placeholder="$t('general.name')"
              :autofocus="isCreate"
              required
            />

            <template #footer>
              <Error :message="form.errors.name" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="form-field mt-lg">
            <template #title>
              <label for="callback_url"
                >{{ $t('webhook.callback_url') }}
                <LabelSuffix :danger="true">*</LabelSuffix>
              </label>
            </template>

            <Input
              id="callback_url"
              v-model="form.callback_url"
              type="url"
              placeholder="https://example.com/webhook"
              required
            />

            <template #footer>
              <Error :message="form.errors.callback_url" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="form-field mt-lg">
            <template #title>
              <label for="method">{{ $t('webhook.method') }}</label>
            </template>

            <Select id="method" v-model="form.method" required>
              <option value="post">POST</option>
              <option value="get">GET</option>
              <option value="put">PUT</option>
              <option value="delete">DELETE</option>
            </Select>

            <template #footer>
              <Error :message="form.errors.method" />
            </template>
          </HorizontalGroup>

          <template v-if="showContentType">
            <HorizontalGroup class="form-field mt-lg">
              <template #title>
                <label for="content_type">{{ $t('webhook.content_type') }}</label>
              </template>

              <Select id="content_type" v-model="form.content_type" required>
                <option value="application/json">application/json</option>
                <option value="application/x-www-form-urlencoded">
                  application/x-www-form-urlencoded
                </option>
              </Select>

              <template #footer>
                <Error :message="form.errors.content_type" />
              </template>
            </HorizontalGroup>
          </template>

          <HorizontalGroup class="form-field mt-lg">
            <template #title>
              <label for="max_attempts">{{ $t('webhook.max_attempts') }}</label>
            </template>

            <Select id="max_attempts" v-model="form.max_attempts" required>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
            </Select>

            <template #footer>
              <Error :message="form.errors.max_attempts" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="form-field mt-lg">
            <template #title>
              {{ $t('general.status') }}
            </template>

            <Flex class="items-center">
              <Checkbox id="active" v-model:checked="form.active" />
              <Label for="active" class="mb-0!">{{ $t('general.active') }}</Label>
            </Flex>

            <template #footer>
              <Error :message="form.errors.active" />
            </template>
          </HorizontalGroup>
        </Panel>

        <Panel class="mt-lg">
          <template #title>{{ $t('general.security') }}</template>

          <template v-if="isCreate">
            <HorizontalGroup>
              <template #title>
                <label for="secret"
                  >{{ $t('webhook.secret') }} ({{ $t('general.optional') }})</label
                >
              </template>

              <Input
                id="secret"
                v-model="form.secret"
                type="text"
                :placeholder="$t('webhook.secret')"
              />

              <template #footer>
                <Error :message="form.errors.secret" />
              </template>
            </HorizontalGroup>
          </template>

          <template v-if="isEdit">
            <ChangeSecret :webhook-id="record.id" />
          </template>
        </Panel>

        <Panel class="mt-lg">
          <template #title>{{ $t('webhook.events') }}</template>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-sm">
            <template v-for="(eventName, eventId) in events" :key="eventId">
              <Flex :responsive="false" class="items-center">
                <Checkbox :id="eventId" v-model:checked="form.events" :value="eventId" />
                <Label :for="eventId" class="mb-0!">{{ eventName }}</Label>
              </Flex>
            </template>
          </div>
        </Panel>

        <div class="flex items-center mt-lg">
          <PrimaryButton type="submit" :is-loading="form.processing" :disabled="form.processing"
            >{{ isCreate ? $t('general.create') : $t('general.update') }}
          </PrimaryButton>
        </div>
      </form>
    </div>
  </div>
</template>
