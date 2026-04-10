<script setup>
import { ref, onMounted, useAttrs, watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import { useI18n } from 'vue-i18n'
import useEditorHelper from '@/Composables/useEditor'
import emitter from '@/Services/emitter'
import History from '@tiptap/extension-history'
import Placeholder from '@tiptap/extension-placeholder'
import Typography from '@tiptap/extension-typography'
import StripLinksOnPaste from '@/Extensions/TipTap/StripLinksOnPaste'
import Hashtag from '@/Extensions/TipTap/Hashtag'
import UserTag from '@/Extensions/TipTap/UserTag'
import Variable from '@/Extensions/TipTap/Variable'
import ClipboardTextParser from '../../Extensions/ProseMirror/ClipboardTextParser'
import PreventLinkDeletion from '@/Extensions/TipTap/PreventLinkDeletion.js'

const { t: $t } = useI18n()

const attrs = useAttrs()

const props = defineProps({
  value: {
    type: [String, null],
    required: true
  },
  editable: {
    type: Boolean,
    default: true
  },
  placeholder: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update'])

const focused = ref(false)

const { defaultExtensions } = useEditorHelper()

const editor = useEditor({
  editable: props.editable,
  content: props.value,
  extensions: [
    ...defaultExtensions,
    ...[
      History,
      Placeholder.configure({
        placeholder: props.placeholder ? props.placeholder : $t('post.start_write')
      }),
      Typography.configure({
        openDoubleQuote: false,
        closeDoubleQuote: false,
        openSingleQuote: false,
        closeSingleQuote: false
      }),
      StripLinksOnPaste,
      Hashtag,
      UserTag,
      Variable,
      PreventLinkDeletion
    ]
  ],
  editorProps: {
    attributes: {
      class: 'focus:outline-hidden min-h-[150px]'
    },
    clipboardTextParser: ClipboardTextParser
  },
  onUpdate: () => {
    emit('update', editor.value.getHTML())
  },
  onFocus: () => {
    focused.value = true
  },
  onBlur: () => {
    focused.value = false
  },
  onCreate: ({ editor }) => {
    editor.view.dom.addEventListener('keydown', keydownHandler)
  }
})

const isEditor = id => {
  return Object.prototype.hasOwnProperty.call(attrs, 'id') && id === attrs.id
}

const moveCursorOutsideOfNonEditable = () => {
  const selection = window.getSelection()
  if (!selection || selection.rangeCount === 0) return

  const range = selection.getRangeAt(0)
  let node = range.startContainer

  // If it's a text node, climb up to its parent
  if (node.nodeType === Node.TEXT_NODE) {
    node = node.parentNode
  }

  while (node) {
    if (node.nodeName === 'A' && node.classList.contains('non_editable')) {
      const anchor = node
      const parent = anchor.parentNode
      if (!parent) return

      // Step 1: Insert a temporary <span> marker after the <a>
      const marker = document.createElement('span')
      marker.innerHTML = '\u200B' // zero-width space to make it visible for caret
      parent.insertBefore(marker, anchor.nextSibling)

      // Step 2: Place the caret inside the marker
      const newRange = document.createRange()
      newRange.setStart(marker.firstChild, 1) // after zero-width space
      newRange.collapse(true)
      selection.removeAllRanges()
      selection.addRange(newRange)

      // Step 3: Optionally simulate pressing space
      document.execCommand('insertText', false, ' ')

      // Step 4: Clean up the marker (optional)
      marker.remove()

      return
    }
    node = node.parentNode
  }
}

const isCursorInsideNonEditable = () => {
  const selection = window.getSelection()
  if (!selection || selection.rangeCount === 0) return false

  const range = selection.getRangeAt(0)
  let node = range.startContainer

  // Get the offset inside the node
  // const offset = range.startOffset

  // If inside a text node, get the parent element
  if (node.nodeType === Node.TEXT_NODE) {
    const parent = node.parentNode
    if (parent.nodeName === 'A' && parent.classList.contains('non_editable')) {
      return true
    }
  }

  // Traverse up the DOM
  while (node) {
    if (node.nodeName === 'A' && node.classList.contains('non_editable')) {
      return true
    }
    node = node.parentNode
  }

  return false
}

const keydownHandler = event => {
  if (
    isCursorInsideNonEditable() &&
    !['ArrowRight', 'ArrowLeft', 'ArrowUp', 'ArrowDown'].includes(event.key)
  ) {
    event.preventDefault()
    moveCursorOutsideOfNonEditable()
  }
}

const focusEditor = () => {
  editor.value?.commands.focus()
}

onMounted(() => {
  emitter.on('insertEmoji', e => {
    if (isEditor(e.editorId)) {
      editor.value.commands.insertContent(e.emoji.native)
    }
  })

  emitter.on('insertContent', e => {
    if (isEditor(e.editorId)) {
      editor.value.commands.insertContent(e.text)
    }
  })

  emitter.on('replaceContent', e => {
    if (isEditor(e.editorId)) {
      editor.value.commands.clearContent()
      editor.value.commands.insertContent(e.text)
    }
  })

  emitter.on('focusEditor', e => {
    if (isEditor(e.editorId)) {
      editor.value.commands.focus()
    }
  })
})

onBeforeUnmount(() => {
  editor.value.destroy()
  emitter.off('insertEmoji')
  emitter.off('insertContent')
  emitter.off('replaceContent')
  emitter.off('focusEditor')
  if (editor.value?.view?.dom) {
    editor.value.view.dom.removeEventListener('keydown', keydownHandler)
  }
})

watch(
  () => props.value,
  value => {
    if (value !== editor.value.getHTML()) {
      editor.value.commands.setContent(value)
    }
  }
)
</script>
<template>
  <div>
    <div v-if="label" class="block font-medium text-gray-700 mb-xs" @click="focusEditor">
      {{ label }}
    </div>
    <div
      :class="{ 'border-primary-200 ring-3 ring-primary-200/50': focused }"
      class="border border-gray-200 rounded-lg p-md pb-xs text-base transition-colors ease-in-out duration-200"
    >
      <EditorContent :editor="editor" />
      <slot />
    </div>
  </div>
</template>
