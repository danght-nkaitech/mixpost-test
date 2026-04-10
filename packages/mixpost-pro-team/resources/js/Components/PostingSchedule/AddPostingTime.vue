<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { WEEKDAYS } from '../Calendar/constants'
import useSettings from '../../Composables/useSettings'
import { cloneDeep } from 'lodash'
import Select from '../Form/Select.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import ExclamationCircle from '../../Icons/ExclamationCircle.vue'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import '@css/overrideFlatPickr.css'
import { captureTimeValueTo24 } from '@/Util/FlatPickr.js'

const props = defineProps({
  modelValue: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const { timeZone, timeFormat } = useSettings()

const form = ref({
  day: 'everyday',
  time: '06:30' // Treat according to the users time zone
})

const timePicker = ref()

const configTimePicker = {
  inline: true,
  timeFormat: 'H:i',
  noCalendar: true,
  enableTime: true,
  time_24hr: timeFormat === 24
}

const handle = () => {
  const times = cloneDeep(props.modelValue)
  const isEveryday = form.value.day === 'everyday'

  form.value.value = captureTimeValueTo24(timePicker.value, timeFormat)

  const updateDayTimes = day => {
    if (!day.times.some(item => item.value === form.value.time)) {
      day.times.push({ value: form.value.time })
    }
  }

  if (isEveryday) {
    times.forEach(updateDayTimes)
  } else {
    const dayIndex = times.findIndex(item => parseInt(item.id) === parseInt(form.value.day))
    if (dayIndex !== -1) {
      updateDayTimes(times[dayIndex])
    }
  }

  emit('update:modelValue', times)
}
</script>
<template>
  <div class="flex flex-col sm:flex-row gap-sm sm:gap-0">
    <div>
      <Select v-model="form.day">
        <option value="everyday">{{ $t('posting_schedule.everyday') }}</option>
        <template v-for="day in WEEKDAYS" :key="day.id">
          <option :value="day.id">{{ $t(`calendar.weekdays.${day.key}.full`) }}</option>
        </template>
      </Select>
    </div>

    <div
      :class="{ 'sm:w-40': timeFormat === 12, 'sm:w-56': timeFormat === 24 }"
      class="pickTime flex items-center sm:justify-center sm:mr-lg sm:-mt-1"
    >
      <div class="mr-xs rtl:mr-0 rtl:ml-lg text-gray-400">{{ $t('general.time') }}</div>
      <div ref="timePicker" class="w-auto">
        <FlatPickr v-model="form.time" :config="configTimePicker" />
      </div>
    </div>

    <div>
      <PrimaryButton @click="handle">{{ $t('posting_schedule.add_post_time') }}</PrimaryButton>
    </div>
  </div>

  <div class="text-sm flex items-center mt-md">
    <div class="mr-xs rtl:mr-0 rtl:ml-xs">{{ timeZone }}</div>

    <Link
      v-tooltip="$t('posting_schedule.posting_times_determined')"
      :href="route('mixpost.profile.index')"
    >
      <ExclamationCircle class="w-4! h-4!" />
    </Link>
  </div>
</template>
