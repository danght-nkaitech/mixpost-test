<script setup>
import { useI18n } from 'vue-i18n'
import { useForm } from '@inertiajs/vue3'
import useNotifications from '@/Composables/useNotifications'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Error from '../Form/Error.vue'
import Label from '../Form/Label.vue'
import InputHidden from '../Form/InputHidden.vue'

const { t: $t } = useI18n()

const { notify } = useNotifications()

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})
const save = () => {
  form.put(route('mixpost.profile.updatePassword'), {
    preserveScroll: true,
    onSuccess() {
      form.reset()
      notify('success', $t('profile.password_changed'))
    }
  })
}
</script>
<template>
  <form @submit.prevent="save">
    <Error v-for="(error, key) in form.errors" :key="key" :message="error" class="mb-xs" />

    <HorizontalGroup class="mt-lg">
      <template #title>
        <Label for="current_password">{{ $t('profile.current_password') }}</Label>
      </template>

      <InputHidden
        id="current_password"
        v-model="form.current_password"
        :error="form.errors.current_password"
        required
      />
    </HorizontalGroup>

    <HorizontalGroup class="mt-md">
      <template #title>
        <Label for="password">{{ $t('profile.new_password') }}</Label>
      </template>

      <InputHidden
        id="password"
        v-model="form.password"
        :error="form.errors.password"
        autocomplete="new-password"
        required
      />
    </HorizontalGroup>

    <HorizontalGroup class="mt-md">
      <template #title>
        <Label for="password_confirmation">{{ $t('profile.confirm_new_password') }}</Label>
      </template>

      <InputHidden
        id="password_confirmation"
        v-model="form.password_confirmation"
        :error="form.password_confirmationpassword"
        autocomplete="new-password"
        required
      />
    </HorizontalGroup>

    <PrimaryButton type="submit" class="mt-lg">{{ $t('general.save') }}</PrimaryButton>
  </form>
</template>
