<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import Settings from '../../../Layouts/Child/Settings.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import Table from '../../../Components/DataDisplay/Table.vue'
import PaymentPlatformItem from '../../../Components/PaymentPlatform/PaymentPlatformItem.vue'
import Alert from '../../../Components/Util/Alert.vue'

const props = defineProps({
  platforms: {
    type: Array,
    required: true
  }
})

const noPaymentEnabled = computed(() => {
  return !props.platforms.some(platform => platform.enabled)
})
</script>

<template>
  <Head :title="$t('panel.pay_plats_settings')" />

  <Settings>
    <div>{{ $t('panel.config_pay_plat') }}</div>

    <Alert v-if="noPaymentEnabled" variant="warning" :closeable="false" class="mt-lg">
      {{ $t('panel.pay_plat_disabled') }}
    </Alert>

    <div class="mt-lg">
      <Panel :with-padding="false">
        <Table>
          <template #body>
            <template v-for="(platform, index) in platforms" :key="index">
              <PaymentPlatformItem :platform="platform" />
            </template>
          </template>
        </Table>
      </Panel>
    </div>
  </Settings>
</template>
