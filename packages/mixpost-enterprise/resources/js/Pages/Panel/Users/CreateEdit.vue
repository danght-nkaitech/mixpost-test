<script setup>
import { computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import usePageMode from '@/Composables/usePageMode'
import useAuth from '@/Composables/useAuth'
import useNotifications from '@/Composables/useNotifications'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Error from '@/Components/Form/Error.vue'
import Input from '@/Components/Form/Input.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import Radio from '../../../Components/Form/Radio.vue'
import LabelSuffix from '../../../Components/Form/LabelSuffix.vue'
import InformationCircle from '../../../Icons/InformationCircle.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import Actions from '../../../Components/User/Actions.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const props = defineProps({
  mode: {
    type: String,
    default: 'create'
  },
  user: {
    type: Object
  }
})

const { notify } = useNotifications()
const { user: authUser } = useAuth()

const pageTitle = computed(() => {
  if (isCreate.value) {
    return $t('user.create_user')
  }

  return $t('user.edit_user')
})

const { isCreate, isEdit } = usePageMode()

const form = useForm({
  name: isEdit.value ? props.user.name : '',
  email: isEdit.value ? props.user.email : '',
  is_admin: isEdit.value ? props.user.is_admin : false,
  password: '',
  password_confirmation: ''
})

const store = () => {
  form.post(route(`${routePrefix}.users.store`), {
    preserveScroll: true,
    preserveState: true
  })
}

const update = () => {
  form.put(route(`${routePrefix}.users.update`, { user: props.user.id }), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      const messages = [$t('user.user_updated'), $t('user.password_changed')]

      notify('success', !form.password ? messages[0] : messages.join('\n'))
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
  <Head :title="pageTitle" />

  <div class="row-py w-full mx-auto">
    <PageHeader :title="pageTitle">
      <template v-if="isEdit">
        <Actions :user="user" :edit="false" />
      </template>
    </PageHeader>

    <div class="row-px">
      <form method="post" @submit.prevent="submit">
        <Panel>
          <template #title>{{ $t('general.detail') }}</template>
          <HorizontalGroup>
            <template #title>
              <label for="name">{{ $t('general.name') }}</label>
            </template>

            <Input
              id="name"
              v-model="form.name"
              :error="form.errors.name"
              type="text"
              class="w-full"
              autofocus
              required
            />

            <template #footer>
              <Error :message="form.errors.name" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-md">
            <template #title>
              <label for="email">{{ $t('general.email') }}</label>
            </template>

            <Input
              id="email"
              v-model="form.email"
              :error="form.errors.email"
              type="email"
              class="w-full"
              autocomplete="off"
              required
            />

            <template #footer>
              <Error :message="form.errors.email" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-md">
            <template #title>
              <Flex>
                {{ $t('user.system_admin') }}
                <LabelSuffix>
                  <InformationCircle v-tooltip="$t('user.admin_access')" />
                </LabelSuffix>
              </Flex>
            </template>

            <div class="flex items-center space-x-sm rtl:space-x-reverse">
              <label>
                <Radio
                  v-model:checked="form.is_admin"
                  :value="false"
                  :disabled="isEdit && user.id === authUser.id"
                />
                {{ $t('general.no') }}</label
              >
              <label>
                <Radio
                  v-model:checked="form.is_admin"
                  :value="true"
                  :disabled="isEdit && user.id === authUser.id"
                />
                {{ $t('general.yes') }}</label
              >
            </div>
          </HorizontalGroup>
        </Panel>

        <Panel class="mt-lg">
          <template #title>{{ $t('general.password') }}</template>
          <template #description>
            <span v-if="isEdit">
              {{ $t('panel.blank_no_change_password') }}
            </span>
          </template>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="password">{{ $t('general.password') }}</label>
            </template>

            <Input
              id="password"
              v-model="form.password"
              :error="form.errors.password"
              type="password"
              class="w-full"
              :required="isCreate"
              autocomplete="new-password"
            />

            <template #footer>
              <Error :message="form.errors.password" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="password_confirmation">{{ $t('general.confirm_password') }}</label>
            </template>

            <Input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :error="form.errors.password_confirmation"
              type="password"
              class="w-full"
              :required="!!form.password"
              autocomplete="new-password"
            />

            <template #footer>
              <Error :message="form.errors.password_confirmation" />
            </template>
          </HorizontalGroup>
        </Panel>

        <PrimaryButton type="submit" :disabled="form.processing" class="mt-lg"
          >{{ isCreate ? $t('general.create') : $t('workspace.update') }}
        </PrimaryButton>
      </form>
    </div>
  </div>
</template>
