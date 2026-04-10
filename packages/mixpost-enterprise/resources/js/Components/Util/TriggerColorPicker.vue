<script setup>
import { computed, ref } from 'vue'
import ColorPicker from '../Package/ColorPicker.vue'
import DialogModal from '../Modal/DialogModal.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'

const props = defineProps({
  color: {
    required: true,
    type: String
  }
})

const emit = defineEmits(['update'])

const show = ref(null)
const selected = ref(null)

const open = () => {
  show.value = true

  selected.value = props.color
}

const close = () => {
  show.value = null
}

const isPickerOpen = computed(() => {
  return show.value !== null
})
const done = () => {
  emit('update', selected.value)

  close()
}
</script>
<template>
  <div role="button" @click="open">
    <slot />
  </div>

  <DialogModal :show="isPickerOpen" max-width="md" @close="close">
    <template #header>
      {{ $t('vendor.color_picker') }}
    </template>
    <template #body>
      <template v-if="isPickerOpen">
        <ColorPicker v-model="selected" />
      </template>
    </template>
    <template #footer>
      <SecondaryButton class="mr-xs" @click="close">{{ $t('general.cancel') }}</SecondaryButton>
      <PrimaryButton @click="done">{{ $t('general.done') }}</PrimaryButton>
    </template>
  </DialogModal>
</template>
