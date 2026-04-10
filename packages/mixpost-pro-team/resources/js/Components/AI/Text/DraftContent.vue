<script setup>
import PureButton from '../../Button/PureButton.vue'
import X from '../../../Icons/X.vue'

defineProps({
  clearable: {
    type: Boolean,
    default: false
  },
  expanded: {
    type: Boolean,
    default: false
  }
})

defineEmits(['clickClear'])
</script>
<template>
  <div
    role="button"
    class="flex justify-between p-sm border border-stone-600 hover:border-stone-700 transition ease-in-out duration-200 w-full rounded-md"
  >
    <div :class="{ 'overflow-hidden text-ellipsis truncate': !expanded }">
      <slot />
    </div>

    <template v-if="clearable">
      <PureButton @click="$emit('clickClear')">
        <X />
      </PureButton>
    </template>
  </div>
</template>
