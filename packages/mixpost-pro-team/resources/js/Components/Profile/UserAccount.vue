<script setup>
import { useI18n } from 'vue-i18n'
import { useForm } from '@inertiajs/vue3'
import useAuth from '../../Composables/useAuth'
import useNotifications from '@/Composables/useNotifications'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Input from '../Form/Input.vue'
import Error from '../Form/Error.vue'
import Label from '../Form/Label.vue'

const { t: $t } = useI18n()

const { user } = useAuth()
const { notify } = useNotifications()

const form = useForm({
  name: user.value.name,
  email: user.value.email
})
const save = () => {
  form.put(route('mixpost.profile.updateUser'), {
    preserveScroll: true,
    onSuccess() {
      notify('success', $t('account.account_updated'))
    }
  })
}
</script>
<template>
  <form @submit.prevent="save">
    <Error v-for="(error, key) in form.errors" :key="key" :message="error" class="mb-xs" />

    <HorizontalGroup>
      <template #title>
        <Label for="name">{{ $t('general.name') }}</Label>
      </template>

      <Input id="name" v-model="form.name" type="text" :error="form.errors.name" />
    </HorizontalGroup>

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="email">{{ $t('general.email') }}</Label>
      </template>

      <Input id="email" v-model="form.email" type="email" :error="form.errors.email" />
    </HorizontalGroup>

    <PrimaryButton type="submit" class="mt-lg">{{ $t('general.save') }}</PrimaryButton>
  </form>
</template>
