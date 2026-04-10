<script setup>
import { inject, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import NProgress from 'nprogress'
import useNotifications from '@/Composables/useNotifications'
import Panel from '@/Components/Surface/Panel.vue'
import ConfirmationModal from '@/Components/Modal/ConfirmationModal.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import DangerButton from '@/Components/Button/DangerButton.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import ProviderIcon from '@/Components/Account/ProviderIcon.vue'
import TrashIcon from '@/Icons/Trash.vue'
import RefreshIcon from '@/Icons/Refresh.vue'

const { t: $t } = useI18n()
const routePrefix = inject('routePrefix')
const { notify } = useNotifications()

defineProps({
  servers: {
    type: Array,
    default: () => []
  }
})

const confirmDeleteServer = ref(null)
const isDeleting = ref(false)

const confirmRecreateServer = ref(null)
const isRecreating = ref(false)

const deleteMastodonService = () => {
  router.delete(route(`${routePrefix}.services.deleteMastodonService`), {
    data: { server: confirmDeleteServer.value },
    preserveScroll: true,
    onStart() {
      isDeleting.value = true
    },
    onSuccess() {
      confirmDeleteServer.value = null
      notify('success', $t('service.mastodon.service_deleted', { service: 'Mastodon' }))
    },
    onFinish() {
      isDeleting.value = false
    }
  })
}

const closeDeleteConfirmation = () => {
  if (isDeleting.value) {
    return
  }

  confirmDeleteServer.value = null
}

const recreateMastodonService = () => {
  NProgress.start()
  isRecreating.value = true

  axios
    .post(route(`${routePrefix}.services.recreateMastodonService`), {
      server: confirmRecreateServer.value
    })
    .then(() => {
      confirmRecreateServer.value = null
      notify('success', $t('service.service_saved', { service: 'Mastodon' }))
    })
    .catch(error => {
      const message = error.response?.data?.error || $t('general.something_went_wrong')
      notify('error', message)
    })
    .finally(() => {
      isRecreating.value = false
      NProgress.done()
    })
}

const closeRecreateConfirmation = () => {
  if (isRecreating.value) {
    return
  }

  confirmRecreateServer.value = null
}
</script>
<template>
  <Panel>
    <template #title>
      <div class="flex items-center">
        <span class="mr-xs">
          <ProviderIcon provider="mastodon" />
        </span>
        <span>Mastodon</span>
      </div>
    </template>

    <template #description>
      {{ $t('service.mastodon.mastodon_apps_desc') }}
    </template>

    <template v-if="servers.length">
      <div class="mt-lg">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left pb-sm font-medium">{{ $t('service.mastodon.server') }}</th>
              <th class="text-right pb-sm font-medium"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="server in servers" :key="server.id" class="border-b border-gray-100">
              <td class="py-sm">{{ server.server }}</td>
              <td class="py-sm text-right">
                <div class="flex items-center justify-end gap-xs">
                  <PrimaryButton size="sm" @click="confirmRecreateServer = server.server">
                    <RefreshIcon class="mr-xs !w-4 !h-4" />
                    {{ $t('service.mastodon.recreate_app') }}
                  </PrimaryButton>
                  <DangerButton size="sm" @click="confirmDeleteServer = server.server">
                    <TrashIcon class="mr-xs !w-4 !h-4" />
                    {{ $t('general.delete') }}
                  </DangerButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <template v-else>
      <div class="mt-lg text-stone-800">
        {{ $t('service.mastodon.no_mastodon_apps') }}
      </div>
    </template>
  </Panel>

  <ConfirmationModal
    :show="confirmDeleteServer !== null"
    variant="danger"
    @close="closeDeleteConfirmation"
  >
    <template #header>{{ $t('service.mastodon.delete_mastodon_app') }}</template>
    <template #body>
      {{ $t('service.mastodon.confirm_delete_mastodon_app') }}
    </template>
    <template #footer>
      <SecondaryButton :disabled="isDeleting" class="mr-xs" @click="closeDeleteConfirmation">
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <DangerButton :is-loading="isDeleting" :disabled="isDeleting" @click="deleteMastodonService">
        {{ $t('general.delete') }}
      </DangerButton>
    </template>
  </ConfirmationModal>

  <ConfirmationModal
    :show="confirmRecreateServer !== null"
    variant="warning"
    @close="closeRecreateConfirmation"
  >
    <template #header>{{ $t('service.mastodon.recreate_mastodon_app') }}</template>
    <template #body>
      {{ $t('service.mastodon.confirm_recreate_mastodon_app') }}
    </template>
    <template #footer>
      <SecondaryButton :disabled="isRecreating" class="mr-xs" @click="closeRecreateConfirmation">
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <PrimaryButton
        :is-loading="isRecreating"
        :disabled="isRecreating"
        @click="recreateMastodonService"
      >
        {{ $t('service.mastodon.recreate_app') }}
      </PrimaryButton>
    </template>
  </ConfirmationModal>
</template>
