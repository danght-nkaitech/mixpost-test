<script setup>
import { ref } from 'vue'
import useSettings from '@/Composables/useSettings.js'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import '@css/overrideFlatPickr.css'
import { captureTimeValueTo24 } from '@/Util/FlatPickr.js'
import Flex from '@/Components/Layout/Flex.vue'
import TimezoneInfo from '@/Components/DataDisplay/TimezoneInfo.vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  showTitle: {
    type: Boolean,
    default: false
  },
  showTimezoneInfo: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const { timeFormat } = useSettings()

const value = ref(props.modelValue)
const el = ref()

const config = {
  inline: true,
  timeFormat: 'H:i',
  noCalendar: true,
  enableTime: true,
  time_24hr: timeFormat === 24
}

const updateModelValue = () => {
  emit('update:modelValue', captureTimeValueTo24(el.value, timeFormat))
}
</script>

<template>
  <Flex :col="true" :class="{ 'justify-center': showTitle }">
    <Flex :class="{ 'items-center justify-center': showTitle }">
      <div v-if="showTitle" class="mr-xs text-gray-400">{{ $t('general.time') }}</div>
      <div ref="el" class="pickTime w-full">
        <FlatPickr :model-value="value" :config="config" @input="updateModelValue" />
      </div>
    </Flex>

    <Flex class="justify-center">
      <TimezoneInfo v-if="showTimezoneInfo" />
    </Flex>
  </Flex>
</template>
