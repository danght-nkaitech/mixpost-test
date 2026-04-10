<script setup>
import { inject, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Input from '@/Components/Form/Input.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import ArrowRightIcon from '@/Icons/ArrowRight.vue'
import ProviderIcon from './ProviderIcon.vue'
import Radio from '../Form/Radio.vue'
import Flex from '../Layout/Flex.vue'
import Error from '../Form/Error.vue'

const workspaceCtx = inject('workspaceCtx')

const form = useForm({
  service: 'bluesky',
  server: ''
})

const open = ref(false)

const connect = () => {
  form
    .transform(data => {
      if (data.service !== 'custom') {
        delete data.server
      }

      return data
    })
    .post(
      route('mixpost.accounts.add', {
        workspace: workspaceCtx.id,
        provider: 'bluesky'
      })
    )
}
</script>
<template>
  <div :class="{ 'bg-bluesky/20': open }">
    <div
      role="button"
      type="button"
      class="w-full flex items-center px-lg py-md hover:bg-bluesky/20 ease-in-out duration-200"
      @click="open = !open"
    >
      <span class="flex mr-md">
        <ProviderIcon provider="bluesky" />
      </span>

      <span class="flex flex-col items-start">
        <span class="font-medium">Bluesky</span>
        <span>{{ $t('service.bluesky.connect_profile') }}</span>
      </span>
    </div>

    <div v-if="open" class="px-lg py-md">
      <form @submit.prevent="connect">
        <HorizontalGroup>
          <template #title>{{ $t('service.bluesky.service') }}</template>

          <Flex>
            <label>
              <Radio v-model:checked="form.service" value="bluesky" />
              Bluesky</label
            >
            <label>
              <Radio v-model:checked="form.service" value="custom" />
              {{ $t('general.custom') }}</label
            >
          </Flex>
        </HorizontalGroup>

        <template v-if="form.service === 'custom'">
          <HorizontalGroup class="mt-lg">
            <template #title>{{ $t('service.bluesky.server_address') }}</template>
            <Input
              v-model="form.server"
              type="url"
              :error="form.errors.server"
              placeholder="https://example.com"
              required
            />
            <template #footer>
              <Error :message="form.errors.server" />
            </template>
          </HorizontalGroup>
        </template>

        <PrimaryButton
          :disabled="(form.service === 'custom' && !form.server) || form.processing"
          :is-loading="form.processing"
          size="md"
          type="submit"
          class="mt-lg"
        >
          <template #icon>
            <ArrowRightIcon />
          </template>

          {{ $t('general.next') }}
        </PrimaryButton>
      </form>
    </div>
  </div>
</template>
