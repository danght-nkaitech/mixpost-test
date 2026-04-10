<script setup>
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import '@css/overrideFlatPickr.css'
import useDateTimePicker from '@/Composables/useDateTimePicker.js'

defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  error: {
    type: Boolean,
    default: false
  },
  inputId: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])

const { getLocaleConfig, getPrevArrow, getNextArrow } = useDateTimePicker()

const config = {
  inline: false,
  dateFormat: 'Y-m-d',
  minDate: 'today',
  allowInput: false,
  monthSelectorType: 'static',
  yearSelectorType: 'static',
  locale: getLocaleConfig(),
  prevArrow: getPrevArrow(),
  nextArrow: getNextArrow()
}
</script>

<template>
  <FlatPickr
    :id="inputId"
    :model-value="modelValue"
    :config="config"
    :class="{ 'border-stone-600': !error, 'border-red-600': error }"
    class="w-full rounded-md focus:border-primary-200 focus:ring-3 focus:ring-primary-200/50 disabled:bg-gray-50 disabled:cursor-not-allowed outline-hidden transition-colors ease-in-out duration-200"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>
