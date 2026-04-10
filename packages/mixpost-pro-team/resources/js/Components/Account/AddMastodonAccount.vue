<script setup>
import { inject, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import useNotifications from '@/Composables/useNotifications'
import Input from '@/Components/Form/Input.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import MastodonIcon from '@/Icons/Mastodon.vue'
import ArrowRightIcon from '@/Icons/ArrowRight.vue'

const workspaceCtx = inject('workspaceCtx')

const { notify } = useNotifications()

const isLoading = ref(false)
const server = ref('')

const open = ref(false)

const createApp = () => {
  return new Promise((resolve, reject) => {
    axios
      .post(route('mixpost.services.createMastodonApp'), { server: server.value })
      .then(() => {
        resolve()
      })
      .catch(function (error) {
        reject(error)
      })
  })
}

const oAuthRedirect = () => {
  isLoading.value = true

  router.post(
    route('mixpost.accounts.add', {
      workspace: workspaceCtx.id,
      provider: 'mastodon'
    }),
    { server: server.value },
    {
      onSuccess() {
        isLoading.value = false
      }
    }
  )
}
const connect = async () => {
  isLoading.value = true

  await createApp()
    .then(() => {
      oAuthRedirect()
    })
    .catch(error => notify('error', error))
    .finally(() => {
      isLoading.value = false
    })
}
</script>
<template>
  <div :class="{ 'bg-mastodon/20': open }">
    <div
      role="button"
      type="button"
      class="w-full flex items-center px-lg py-md hover:bg-mastodon/20 ease-in-out duration-200"
      @click="open = !open"
    >
      <span class="flex mr-md">
        <MastodonIcon class="text-mastodon" />
      </span>

      <span class="flex flex-col items-start">
        <span class="font-medium">Mastodon</span>
        <span>{{ $t('service.mastodon.connect_profile') }}</span>
      </span>
    </div>

    <div v-if="open" class="px-lg py-md">
      <HorizontalGroup>
        <template #title>{{ $t('service.mastodon.enter_server') }}</template>
        <Input v-model="server" type="text" placeholder="example.server" />
      </HorizontalGroup>

      <PrimaryButton
        :disabled="!server || isLoading"
        :is-loading="isLoading"
        size="md"
        class="mt-lg"
        @click="connect"
      >
        <template #icon>
          <ArrowRightIcon />
        </template>
        {{ $t('general.next') }}
      </PrimaryButton>
    </div>
  </div>
</template>
