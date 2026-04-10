<script setup>
import { Dropdown as VDropdown } from 'floating-vue'
import '@css/overrideVDropdown.css'
import { ImageDisplay } from './util'
import PureButton from '../../../Components/Button/PureButton.vue'
import Trash from '../../../Icons/Trash.vue'
import AlignRight from '../../../Icons/Editor/AlignRight.vue'
import AlignCenter from '../../../Icons/Editor/AlignCenter.vue'
import AlignLeft from '../../../Icons/Editor/AlignLeft.vue'
import { computed } from 'vue'
import { Editor, nodeViewProps } from '@tiptap/vue-3'

const props = defineProps({
  editor: {
    required: true,
    type: Editor
  },
  node: nodeViewProps['node'],
  updateAttributes: nodeViewProps['updateAttributes'],
  deleteNode: {
    required: true,
    type: Function
  },
  selected: {
    required: true,
    type: Boolean
  }
})

const currentDisplay = computed(() => {
  return props.node.attrs.display
})

const updateDisplay = display => {
  if (display === currentDisplay.value) {
    props.updateAttributes({
      display: ImageDisplay.INLINE
    })

    return
  }

  props.updateAttributes({
    display
  })
}

const remove = () => {
  props.deleteNode()
}
</script>
<template>
  <VDropdown :triggers="[]" :shown="selected" :auto-hide="false">
    <template #popper>
      <div class="p-md bg-white">
        <div class="editorClassicMenu flex gap-xs">
          <button
            v-tooltip="$t('editor.align_left')"
            :class="{ 'is-active': ImageDisplay.FLOAT_LEFT === currentDisplay }"
            type="button"
            @click="updateDisplay(ImageDisplay.FLOAT_LEFT)"
          >
            <AlignLeft />
          </button>

          <button
            v-tooltip="$t('editor.align_center')"
            :class="{ 'is-active': ImageDisplay.CENTER === currentDisplay }"
            type="button"
            @click="updateDisplay(ImageDisplay.CENTER)"
          >
            <AlignCenter />
          </button>

          <button
            v-tooltip="$t('editor.align_right')"
            :class="{ 'is-active': ImageDisplay.FLOAT_RIGHT === currentDisplay }"
            type="button"
            @click="updateDisplay(ImageDisplay.FLOAT_RIGHT)"
          >
            <AlignRight />
          </button>

          <PureButton v-tooltip="$t('editor.remove_image')" destructive @click="remove">
            <Trash />
          </PureButton>
        </div>
      </div>
    </template>
  </VDropdown>
</template>
