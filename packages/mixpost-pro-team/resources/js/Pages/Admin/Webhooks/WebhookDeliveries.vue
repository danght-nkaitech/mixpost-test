<script setup>
import { inject, onMounted, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/Admin.vue'
import { cloneDeep, pickBy, throttle } from 'lodash'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import WebhookAction from '../../../Components/Webhook/WebhookAction.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import Pagination from '../../../Components/Navigation/Pagination.vue'
import NoResult from '../../../Components/Util/NoResult.vue'
import WebhookDeliveryItem from '../../../Components/Webhook/WebhookDeliveryItem.vue'
import WebhookDeliveryItemView from '../../../Components/Webhook/WebhookDeliveryItemView.vue'
import Tabs from '../../../Components/Navigation/Tabs.vue'
import Tab from '../../../Components/Navigation/Tab.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  filter: {
    type: Object,
    default: () => ({})
  },
  webhook: {
    type: Object,
    required: true
  },
  deliveries: {
    type: Object
  }
})

const workspaceCtx = inject('workspaceCtx')

const filter = ref({
  status: props.filter.status
})

const selectedDelivery = ref(null)

onMounted(() => {
  if (props.deliveries.data.length) {
    selectDelivery(props.deliveries.data[0])
  }
})

const selectDelivery = item => {
  selectedDelivery.value = item
}

watch(
  () => cloneDeep(filter.value),
  throttle(() => {
    router.get(
      route('mixpost.system.webhooks.deliveries.index', {
        workspace: workspaceCtx.id,
        webhook: props.webhook.id
      }),
      pickBy(filter.value),
      {
        preserveState: true,
        preserveScroll: true,
        only: ['deliveries', 'filter']
      }
    )
  }, 300)
)
</script>
<template>
  <Head :title="$t('webhook.deliveries')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('webhook.deliveries')">
      <template #description>
        {{ webhook.name }}
        <div class="text-gray-500">
          {{ webhook.callback_url }}
        </div>
      </template>

      <WebhookAction :record="webhook" :create="false" :deliveries="false" />
    </PageHeader>

    <div class="row-px">
      <div class="w-full mt-lg">
        <Tabs>
          <Tab :active="!$page.props.filter.status" @click="filter.status = null"
            >{{ $t('general.all') }}
          </Tab>
          <Tab :active="$page.props.filter.status === 'success'" @click="filter.status = 'success'">
            {{ $t('general.succeeded') }}
          </Tab>
          <Tab :active="$page.props.filter.status === 'error'" @click="filter.status = 'error'">
            {{ $t('general.failed') }}
          </Tab>
        </Tabs>
      </div>

      <Panel class="mt-lg" :with-padding="false">
        <Flex gap="gap-0">
          <div class="w-full md:w-1/2 border-r border-gray-100">
            <template v-for="item in deliveries.data" :key="item.id">
              <WebhookDeliveryItem
                :item="item"
                :active="selectedDelivery && selectedDelivery.id === item.id"
                @click="selectDelivery(item)"
              />
            </template>

            <NoResult v-if="!deliveries.meta.total" class="py-md px-md" />
          </div>

          <div class="w-full md:w-1/2">
            <template v-if="selectedDelivery">
              <WebhookDeliveryItemView :webhook-id="webhook.id" :delivery="selectedDelivery" />
            </template>
          </div>
        </Flex>
      </Panel>

      <div v-if="deliveries.meta.links.length > 3" class="mt-lg">
        <Pagination :meta="deliveries.meta" :links="deliveries.links" />
      </div>
    </div>
  </div>
</template>
