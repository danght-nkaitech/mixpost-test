<script setup>
import { useI18n } from 'vue-i18n'
import Flex from '../Layout/Flex.vue'
import Clock from '../../Icons/Clock.vue'
import Check from '../../Icons/Check.vue'
import XIcon from '../../Icons/X.vue'

const { t: $t } = useI18n()

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])

const statusText = () => {
  if (props.item.status === 'ERROR' && props.item.resend_at) {
    return $t('webhook.delivery_failed_try_redeliver')
  }

  if (props.item.status === 'ERROR') {
    return $t('webhook.delivery_failed')
  }

  return ''
}
</script>
<template>
  <Flex
    :class="{ 'bg-gray-50': active }"
    class="px-md py-sm border-b last:border-b-0 border-gray-100 justify-between cursor-pointer hover:bg-gray-50 transition ease-in-out duration-200"
    @click="$emit('click')"
  >
    <Flex gap="gap-sm">
      <div>
        <template v-if="item.status === 'ERROR'">
          <span v-tooltip="statusText()" class="text-red-500">
            <template v-if="item.resend_at"><Clock /></template>
            <template v-else><XIcon /></template>
          </span>
        </template>

        <template v-if="item.status === 'SUCCESS'">
          <Check class="text-green-500" />
        </template>
      </div>
      <div>{{ $t(`webhook.event.${item.event}`) }}</div>
    </Flex>
    <div>{{ item.created_at }}</div>
  </Flex>
</template>
