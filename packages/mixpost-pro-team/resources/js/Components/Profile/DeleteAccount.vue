<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { router, usePage } from '@inertiajs/vue3'
import DangerButton from '../Button/DangerButton.vue'
import useRouter from '../../Composables/useRouter'

const { t: $t } = useI18n()

const confirmation = inject('confirmation')

const { onError } = useRouter()

const openDeleteAccountConfirmation = () => {
  confirmation()
    .title($t('profile.delete_account'))
    .description($t('profile.delete_account_desc'))
    .destructive()
    .btnConfirmName($t('profile.delete_account'))
    .onConfirm(dialog => {
      dialog.isLoading(true)

      deleteAccount(dialog)
    })
    .show()
}

const deleteAccount = dialog => {
  router.delete(usePage().props.delete_account_url, {
    onSuccess() {
      dialog.reset()
    },
    onError(error) {
      dialog.isLoading(false)

      onError(error, () => {
        dialog.isLoading(true)
        deleteAccount(dialog)
      })
    }
  })
}
</script>
<template>
  <DangerButton @click="openDeleteAccountConfirmation">{{
    $t('profile.delete_account')
  }}</DangerButton>
</template>
