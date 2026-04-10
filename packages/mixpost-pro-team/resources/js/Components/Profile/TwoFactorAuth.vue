<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'
import useNotifications from '@/Composables/useNotifications'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Input from '../Form/Input.vue'
import Label from '../Form/Label.vue'
import Badge from '../DataDisplay/Badge.vue'
import Flex from '../Layout/Flex.vue'
import ConfirmPassword from './ConfirmPassword.vue'
import DangerButton from '../Button/DangerButton.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'

const { t: $t } = useI18n()

const setup = ref({
  svg: '',
  secret_key: ''
})
const forceActive = ref(false)
const forceDisable = ref(false)
const codeConfirmation = ref('')
const showQrCode = ref(false)
const showRecoveryCodes = ref(false)
const recoveryCodes = ref([])
const showConfirmPassword = ref(false)
const functionToContinue = ref(null)

const routePrefix = inject('routePrefix')
const { notify } = useNotifications()

const isActive = computed(() => {
  if (forceDisable.value) {
    return false
  }

  return usePage().props.userHasTwoFactorAuthEnabled || forceActive.value === true
})

const openConfirmPassword = () => {
  showConfirmPassword.value = true
}

const closeConfirmPassword = () => {
  showConfirmPassword.value = false
}

const enableIsLoading = ref(false)
const enable = () => {
  enableIsLoading.value = true
  axios
    .post(route(`${routePrefix}.profile.two-factor-auth.enable`))
    .then(response => {
      showQrCode.value = true
      setup.value = response.data
    })
    .catch(error => catchErrors(error, enable))
    .finally(() => (enableIsLoading.value = false))
}

const confirmIsLoading = ref(false)
const confirm = () => {
  confirmIsLoading.value = true
  axios
    .post(route(`${routePrefix}.profile.two-factor-auth.confirm`), {
      code: codeConfirmation.value
    })
    .then(response => {
      forceActive.value = true
      forceDisable.value = false
      showQrCode.value = false
      recoveryCodes.value = response.data.recovery_codes
      showRecoveryCodes.value = true
      codeConfirmation.value = ''
      notify('success', $t('profile.two_factor_enabled'))
    })
    .catch(error => catchErrors(error, confirm))
    .finally(() => (confirmIsLoading.value = false))
}

const getRecoveryCodesIsLoading = ref(false)
const getRecoveryCodes = () => {
  getRecoveryCodesIsLoading.value = true
  axios
    .get(route(`${routePrefix}.profile.two-factor-auth.showRecoveryCodes`))
    .then(response => {
      recoveryCodes.value = response.data.recovery_codes
      showRecoveryCodes.value = true
    })
    .catch(error => catchErrors(error, getRecoveryCodes))
    .finally(() => (getRecoveryCodesIsLoading.value = false))
}

const regenerateRecoveryCodesIsLoading = ref(false)
const regenerateRecoveryCodes = () => {
  regenerateRecoveryCodesIsLoading.value = true
  axios
    .post(route(`${routePrefix}.profile.two-factor-auth.regenerateRecoveryCodes`))
    .then(response => {
      recoveryCodes.value = response.data.recovery_codes
      showRecoveryCodes.value = true
    })
    .catch(error => catchErrors(error, regenerateRecoveryCodes))
    .finally(() => (regenerateRecoveryCodesIsLoading.value = false))
}

const disableIsLoading = ref(false)
const disable = () => {
  disableIsLoading.value = true
  axios
    .delete(route(`${routePrefix}.profile.two-factor-auth.disable`))
    .then(() => {
      forceDisable.value = true
      notify('success', $t('profile.two_factor_disabled'))
    })
    .catch(error => catchErrors(error, disable))
    .finally(() => (disableIsLoading.value = false))
}

const catchErrors = (error, fnc) => {
  if (error.response.status === 422) {
    if (error.response.data.errors.confirm_password) {
      functionToContinue.value = fnc
      openConfirmPassword()
    }

    if (error.response.data.errors.code) {
      notify('error', error.response.data.errors.code[0])
    }

    return
  }

  notify('error', $t('error.something_wrong'))
}
</script>
<template>
  <div>
    <div>
      {{ $t('profile.install_totp') }}
    </div>

    <div class="mt-lg">
      <template v-if="isActive">
        <Badge variant="success">{{ $t('general.enabled') }}</Badge>
      </template>

      <template v-if="!isActive">
        <Badge variant="error">{{ $t('general.disabled') }}</Badge>
      </template>
    </div>

    <div v-if="showQrCode" class="mt-lg">
      <Flex class="w-full relative rounded-lg border border-gray-200 p-xs">
        <div v-html="setup.svg" />

        <Flex class="justify-center items-center w-full">
          <div class="uppercase font-medium text-center">
            <div class="text-gray-500">{{ $t('profile.secret_key') }}</div>
            <div>{{ setup.secret_key }}</div>
          </div>
        </Flex>
      </Flex>

      <div class="mt-md">{{ $t('profile.scan_qr_code') }}</div>

      <HorizontalGroup class="mt-lg">
        <template #title>
          <Label for="code">{{ $t('profile.code') }}</Label>
        </template>

        <Input id="code" v-model="codeConfirmation" type="text" class="w-full" autocomplete="off" />
      </HorizontalGroup>

      <Flex>
        <PrimaryButton
          :disabled="confirmIsLoading"
          :is-loading="confirmIsLoading"
          class="mt-lg"
          @click="confirm"
          >{{ $t('general.confirm') }}
        </PrimaryButton>

        <SecondaryButton
          class="mt-lg"
          @click="
            () => {
              showQrCode = false
              codeConfirmation = ''
            }
          "
          >{{ $t('general.cancel') }}
        </SecondaryButton>
      </Flex>
    </div>

    <div v-if="isActive">
      <div v-if="showRecoveryCodes" class="mt-lg">
        <div>{{ $t('profile.store_recovery_codes') }}</div>

        <div class="mt-xs bg-primary-50 rounded-lg p-md">
          <div v-for="code in recoveryCodes" :key="code" class="mb-xs">
            <div class="font-medium">{{ code }}</div>
          </div>
        </div>
      </div>

      <Flex class="mt-lg">
        <template v-if="showRecoveryCodes">
          <PrimaryButton
            :disabled="regenerateRecoveryCodesIsLoading"
            :is-loading="regenerateRecoveryCodesIsLoading"
            @click="regenerateRecoveryCodes"
          >
            {{ $t('profile.regenerate_recovery_codes') }}
          </PrimaryButton>
        </template>

        <template v-if="!showRecoveryCodes">
          <PrimaryButton
            :disabled="getRecoveryCodesIsLoading"
            :is-loading="getRecoveryCodesIsLoading"
            @click="getRecoveryCodes"
            >{{ $t('profile.show_recovery_codes') }}
          </PrimaryButton>
        </template>

        <DangerButton :disabled="disableIsLoading" :is-loading="disableIsLoading" @click="disable"
          >{{ $t('general.disable') }}
        </DangerButton>
      </Flex>
    </div>

    <template v-if="!isActive && !showQrCode">
      <PrimaryButton
        :disabled="enableIsLoading"
        :is-loading="enableIsLoading"
        class="mt-lg"
        @click="enable"
        >{{ $t('general.enable') }}
      </PrimaryButton>
    </template>
  </div>

  <ConfirmPassword
    :show="showConfirmPassword"
    @confirm="
      () => {
        closeConfirmPassword()

        if (functionToContinue) {
          functionToContinue()
        }
      }
    "
    @close="closeConfirmPassword"
  />
</template>
