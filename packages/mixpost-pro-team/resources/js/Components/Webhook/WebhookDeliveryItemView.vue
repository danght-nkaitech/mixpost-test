<script setup>
import { computed, inject, onMounted, ref, watch } from 'vue'
import HighlightCore from 'highlight.js/lib/core'
import HighlightJsonLanguage from 'highlight.js/lib/languages/json'
import pretty from 'pretty'
import beautify from 'js-beautify'
import 'highlight.js/styles/github.css'
import NProgress from 'nprogress'
import useHttpClient from '../../Composables/useHttpClient'
import Flex from '../Layout/Flex.vue'
import Preloader from '../Util/Preloader.vue'
import SectionTitle from '../DataDisplay/SectionTitle.vue'
import SectionBorder from '../Surface/SectionBorder.vue'
import Refresh from '../../Icons/Refresh.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import { router } from '@inertiajs/vue3'
import useRouter from '../../Composables/useRouter'

HighlightCore.registerLanguage('json', HighlightJsonLanguage)

const props = defineProps({
  webhookId: {
    type: String,
    required: true
  },
  delivery: {
    type: Object,
    required: true
  }
})

const routePrefix = inject('routePrefix')
const workspaceCtx = inject('workspaceCtx')

const { onCatch } = useHttpClient()
const { onError } = useRouter()

const isLoading = ref(false)
const additionalData = ref({
  response: {},
  payload: {}
})

onMounted(async () => {
  await fetchAdditionalData()
})

watch(
  () => props.delivery,
  async () => {
    await fetchAdditionalData()
  }
)

const highlightedResponse = computed(() => {
  if (!additionalData.value.response) {
    return
  }

  return highlightCode(additionalData.value.response)
})

const highlightedPayload = computed(() => {
  if (!additionalData.value.payload) {
    return
  }

  return highlightCode(additionalData.value.payload)
})

const highlightCode = data => {
  const code =
    typeof data === 'string'
      ? pretty(data, { ocd: true })
      : beautify(JSON.stringify(data), { indent_size: '', space_in_empty_paren: true })

  return HighlightCore.highlight(code, { language: 'json' }).value
}

const getRoute = name => {
  switch (name) {
    case 'show':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.deliveries.show`, {
            workspace: workspaceCtx.id,
            webhook: props.webhookId,
            delivery: props.delivery.id
          })
        : route(`${routePrefix}.system.webhooks.deliveries.show`, {
            webhook: props.webhookId,
            delivery: props.delivery.id
          })
    case 'resend':
      return workspaceCtx
        ? route(`${routePrefix}.webhooks.deliveries.resend`, {
            workspace: workspaceCtx.id,
            webhook: props.webhookId,
            delivery: props.delivery.id
          })
        : route(`${routePrefix}.system.webhooks.deliveries.resend`, {
            webhook: props.webhookId,
            delivery: props.delivery.id
          })
    default:
      return ''
  }
}

const fetchAdditionalData = () => {
  return new Promise((resolve, reject) => {
    NProgress.start()
    isLoading.value = true

    axios
      .get(getRoute('show'))
      .then(response => {
        additionalData.value = {
          response: response.data.response,
          payload: response.data.payload
        }

        resolve(response)
      })
      .catch(error => {
        onCatch(error)
        reject(error)
      })
      .finally(() => {
        NProgress.done()
        isLoading.value = false
      })
  })
}

const resend = () => {
  router.post(
    getRoute('resend'),
    {},
    {
      preserveState: true,
      preserveScroll: true,
      only: ['deliveries'],
      onError
    }
  )
}
</script>
<template>
  <div class="px-lg pt-lg">
    <Flex>
      <Flex class="w-full items-center">
        <SectionTitle>{{ $t(`webhook.event.${delivery.event}`) }}</SectionTitle>
        <template v-if="delivery.resent_manually">
          <div v-tooltip="$t('webhook.resend_manually')" class="select-none">
            <Refresh class="text-gray-500 w-5! h-5!" />
          </div>
        </template>
      </Flex>

      <SecondaryButton size="sm" @click="resend">
        <template #icon>
          <Refresh />
        </template>
        {{ $t('webhook.resend') }}
      </SecondaryButton>
    </Flex>

    <template v-if="delivery.resend_at">
      <Flex class="mt-sm text-gray-500" gap="gap-lg">
        <div>{{ $t('webhook.next_retry') }}</div>
        <div>{{ delivery.resend_at }}</div>
      </Flex>
    </template>
  </div>

  <SectionBorder :lighter="true" />

  <div class="px-lg">
    <SectionTitle>{{ $t('webhook.response') }}</SectionTitle>

    <Flex class="mt-lg gap-xl">
      <div class="text-gray-500">{{ $t('webhook.http_status') }}</div>
      <div>{{ delivery.http_status }}</div>
    </Flex>

    <div class="relative min-h-[50px]">
      <template v-if="isLoading">
        <Preloader />
      </template>

      <template v-if="!isLoading">
        <pre class="border-0 p-0 inline-grid">
                    <code class="hljs json text-sm p-0!" v-html="highlightedResponse"/>
               </pre>
      </template>
    </div>
  </div>

  <SectionBorder />

  <div class="px-lg pb-lg">
    <SectionTitle>{{ $t('webhook.payload') }}</SectionTitle>

    <div class="relative min-h-[50px]">
      <template v-if="isLoading">
        <Preloader />
      </template>

      <template v-if="!isLoading">
        <pre class="border-0 p-0 inline-grid">
                   <code class="hljs json text-sm p-0!" v-html="highlightedPayload"/>
               </pre>
      </template>
    </div>
  </div>
</template>
