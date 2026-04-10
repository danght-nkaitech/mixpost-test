<script setup>
import { inject, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { EditorContent, useEditor } from '@tiptap/vue-3'
import useEditorHelper from '@/Composables/useEditor'
import History from '@tiptap/extension-history'
import HardBreak from '@tiptap/extension-hard-break'
import Placeholder from '@tiptap/extension-placeholder'
import Typography from '@tiptap/extension-typography'
import Mention from '@tiptap/extension-mention'
import StripLinksOnPaste from '@/Extensions/TipTap/StripLinksOnPaste'
import Suggestion from '../../Extensions/TipTap/Suggestion'
import ClipboardTextParser from '../../Extensions/ProseMirror/ClipboardTextParser'

const { t: $t } = useI18n()

const props = defineProps({
  modelValue: {
    required: true,
    type: String
  },
  editable: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur', 'enter'])

const workspaceCtx = inject('workspaceCtx')

const { defaultExtensions, isDocEmpty } = useEditorHelper()

const editor = useEditor({
  editable: props.editable,
  content: props.modelValue,
  extensions: [
    ...defaultExtensions,
    ...[
      History,
      HardBreak,
      Placeholder.configure({
        placeholder: $t('post_activity.write_comment')
      }),
      Typography.configure({
        openDoubleQuote: false,
        closeDoubleQuote: false,
        openSingleQuote: false,
        closeSingleQuote: false
      }),
      StripLinksOnPaste,
      Mention.configure({
        suggestion: Suggestion
      })
    ]
  ],
  editorProps: {
    workspaceId: workspaceCtx?.id,
    attributes: {
      class: 'focus:outline-hidden min-h-[10px] max-h-96 overflow-y-auto'
    },
    clipboardTextParser: ClipboardTextParser,
    handleDOMEvents: {
      keydown: (view, event) => {
        if (!event.shiftKey && event.which === 13 && !view.state.mention$.active) {
          emit('enter')
          event.preventDefault()
        }
      }
    }
  },
  onUpdate: () => {
    const value = editor.value.getHTML()

    if (isDocEmpty(value)) {
      emit('update:modelValue', '')
      return
    }

    emit('update:modelValue', value)
  },
  onFocus: () => {
    emit('focus')
  },
  onBlur: () => {
    emit('blur')
  }
})

onUnmounted(() => {
  editor.value.destroy()
})

defineExpose({ editor })
</script>
<template>
  <EditorContent :editor="editor" />
</template>
<style lang="css">
@reference "@css/app.css";
[data-type='mention'] {
  @apply text-primary-500;
}
</style>
