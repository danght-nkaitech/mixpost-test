<script setup>
import { ref } from 'vue'
import Flex from '../Layout/Flex.vue'
import ClipboardButton from './ClipboardButton.vue'

const dom = ref('')
</script>
<template>
  <Flex :responsive="false" class="md:items-center">
    <div
      class="px-sm py-xs bg-gray-100 flex items-center justify-between max-w-(--container-md) rounded-lg"
    >
      <code ref="dom" class="max-w-full overflow-x-auto relative z-10">
        <slot />
      </code>
    </div>

    <ClipboardButton :html-elm="dom" component="PureButton" />
  </Flex>
</template>
