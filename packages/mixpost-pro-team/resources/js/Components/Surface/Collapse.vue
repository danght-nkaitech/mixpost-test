<script setup>
import { ref } from 'vue'
import ChevronDown from '../../Icons/ChevronDown.vue'

defineProps({
  colorClass: {
    type: String,
    default: 'bg-gray-50 border-gray-200 hover:bg-gray-100'
  }
})
const state = ref(false)
</script>
<template>
  <div :class="colorClass" class="border rounded-lg transition-colors ease-in-out duration-200">
    <div
      role="button"
      class="cursor-pointer p-md flex justify-between items-center"
      @click="state = !state"
    >
      <div>
        <div>
          <slot name="title" />
        </div>

        <div v-if="$slots.description" class="text-sm text-gray-500">
          <slot name="description" />
        </div>
      </div>

      <div>
        <ChevronDown
          class="transform transition-transform ease-in-out duration-300"
          :class="{ 'rotate-180': state }"
        />
      </div>
    </div>

    <div
      v-if="state"
      class="bg-white px-md py-md w-full flex justify-start mt-xs sm:mt-0 rounded-b-lg"
    >
      <slot />
    </div>
  </div>
</template>
