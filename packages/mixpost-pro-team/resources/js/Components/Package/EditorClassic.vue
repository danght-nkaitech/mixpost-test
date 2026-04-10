<script setup>
import { ref, onUnmounted, watch, computed } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'
import Image from '../../Extensions/TipTap/Image/Image'
import Link from '@tiptap/extension-link'
import Dropdown from '../Dropdown/Dropdown.vue'
import DropdownItem from '../Dropdown/DropdownItem.vue'
import Bold from '../../Icons/Editor/Bold.vue'
import Italic from '../../Icons/Editor/Italic.vue'
import UnderlineIcon from '../../Icons/Editor/Underline.vue'
import Strike from '../../Icons/Editor/Strike.vue'
import BulletList from '../../Icons/Editor/BulletList.vue'
import OrderedList from '../../Icons/Editor/OrderedList.vue'
import Blockquote from '../../Icons/Editor/Blockquote.vue'
import PageBreak from '../../Icons/Editor/PageBreak.vue'
import AlignLeft from '../../Icons/Editor/AlignLeft.vue'
import AlignRight from '../../Icons/Editor/AlignRight.vue'
import AlignCenter from '../../Icons/Editor/AlignCenter.vue'
import AlignJustify from '../../Icons/Editor/AlignJustify.vue'
import Photo from '../../Icons/Photo.vue'
import UploadButton from '../Media/UploadButton.vue'
import InsertLink from '../EditorButtons/InsertLink.vue'
import HorizontalLine from '../../Icons/Editor/HorizontalLine.vue'
import ClipboardTextParserParagraph from '../../Extensions/ProseMirror/ClipboardTextParserParagraph'

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
  }
})

const emit = defineEmits(['update'])

const focused = ref(false)

const editor = useEditor({
  editable: props.editable,
  content: props.value,
  extensions: [
    StarterKit,
    Underline,
    TextAlign.configure({
      types: ['heading', 'paragraph']
    }),
    Image,
    Link.configure({
      openOnClick: false
    })
  ],
  editorProps: {
    attributes: {
      class: 'focus:outline-hidden min-h-[150px]'
    },
    clipboardTextParser: ClipboardTextParserParagraph
  },
  onUpdate: () => {
    emit('update', editor.value.getHTML())
  },
  onFocus: () => {
    focused.value = true
  },
  onBlur: () => {
    focused.value = false
  }
})

onUnmounted(() => {
  editor.value.destroy()
})

watch(
  () => props.value,
  value => {
    if (value !== editor.value.getHTML()) {
      editor.value.commands.setContent(value)
    }
  }
)

const activeFormat = computed(() => {
  if (editor.value.isActive('heading', { level: 1 })) {
    return 'Heading 1'
  }

  if (editor.value.isActive('heading', { level: 2 })) {
    return 'Heading 2'
  }

  if (editor.value.isActive('heading', { level: 3 })) {
    return 'Heading 3'
  }

  if (editor.value.isActive('heading', { level: 4 })) {
    return 'Heading 4'
  }

  if (editor.value.isActive('heading', { level: 5 })) {
    return 'Heading 5'
  }

  if (editor.value.isActive('heading', { level: 6 })) {
    return 'Heading 6'
  }

  return 'Paragraph'
})

const addImage = src => {
  editor.value.chain().focus().setImage({ src }).run()
}

// const insertLink = ({ url, target }) => {
//   editor.value.commands.setLink({ href: url, target })
// }
</script>
<template>
  <div
    :class="{ 'border-primary-200 ring-3 ring-primary-200/50': focused }"
    class="editorClassic border border-gray-200 rounded-lg p-md pb-xs text-base transition-colors ease-in-out duration-200"
  >
    <div
      v-if="editor"
      class="editorClassicMenu text-stone-800 flex flex-wrap gap-xs border-b border-gray-200 pb-md mb-md"
    >
      <Dropdown width-classes="w-40" placement="bottom-start">
        <template #trigger>
          <button type="button" class="w-28 text-left h-full">{{ activeFormat }}</button>
        </template>

        <template #content>
          <DropdownItem
            :is-active="editor.isActive('paragraph')"
            size="xs"
            as="button"
            @click="editor.chain().focus().setParagraph().run()"
          >
            Paragraph
          </DropdownItem>

          <DropdownItem
            :is-active="editor.isActive('heading', { level: 1 })"
            size="xs"
            as="button"
            @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
          >
            <span class="text-2xl">Heading 1</span>
          </DropdownItem>

          <DropdownItem
            :is-active="editor.isActive('heading', { level: 2 })"
            size="xs"
            as="button"
            @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
          >
            <span class="text-xl">Heading 2</span>
          </DropdownItem>

          <DropdownItem
            :is-active="editor.isActive('heading', { level: 3 })"
            size="xs"
            as="button"
            @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
          >
            <span class="text-lg">Heading 3</span>
          </DropdownItem>

          <DropdownItem
            :is-active="editor.isActive('heading', { level: 4 })"
            size="xs"
            as="button"
            @click="editor.chain().focus().toggleHeading({ level: 4 }).run()"
          >
            <span class="text-md">Heading 4</span>
          </DropdownItem>

          <DropdownItem
            :is-active="editor.isActive('heading', { level: 5 })"
            size="xs"
            as="button"
            @click="editor.chain().focus().toggleHeading({ level: 5 }).run()"
          >
            <span class="text-base">Heading 5</span>
          </DropdownItem>

          <DropdownItem
            :is-active="editor.isActive('heading', { level: 6 })"
            size="xs"
            as="button"
            @click="editor.chain().focus().toggleHeading({ level: 6 }).run()"
          >
            <span class="text-sm">Heading 6</span>
          </DropdownItem>
        </template>
      </Dropdown>
      <button
        :disabled="!editor.can().chain().focus().toggleBold().run()"
        :class="{ 'is-active': editor.isActive('bold') }"
        type="button"
        @click="editor.chain().focus().toggleBold().run()"
      >
        <Bold />
      </button>
      <button
        :disabled="!editor.can().chain().focus().toggleItalic().run()"
        :class="{ 'is-active': editor.isActive('italic') }"
        type="button"
        @click="editor.chain().focus().toggleItalic().run()"
      >
        <Italic />
      </button>
      <button
        :disabled="!editor.can().chain().focus().toggleItalic().run()"
        :class="{ 'is-active': editor.isActive('underline') }"
        type="button"
        @click="editor.chain().focus().toggleUnderline().run()"
      >
        <UnderlineIcon />
      </button>
      <button
        :disabled="!editor.can().chain().focus().toggleStrike().run()"
        :class="{ 'is-active': editor.isActive('strike') }"
        class="mr-sm"
        type="button"
        @click="editor.chain().focus().toggleStrike().run()"
      >
        <Strike />
      </button>

      <button
        :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }"
        type="button"
        @click="editor.chain().focus().setTextAlign('left').run()"
      >
        <AlignLeft />
      </button>

      <button
        :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }"
        type="button"
        @click="editor.chain().focus().setTextAlign('center').run()"
      >
        <AlignCenter />
      </button>

      <button
        :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }"
        type="button"
        @click="editor.chain().focus().setTextAlign('right').run()"
      >
        <AlignRight />
      </button>

      <button
        :class="{ 'is-active': editor.isActive({ textAlign: 'justify' }) }"
        class="mr-sm"
        type="button"
        @click="editor.chain().focus().setTextAlign('justify').run()"
      >
        <AlignJustify />
      </button>

      <button
        :class="{ 'is-active': editor.isActive('bulletList') }"
        type="button"
        @click="editor.chain().focus().toggleBulletList().run()"
      >
        <BulletList />
      </button>

      <button
        :class="{ 'is-active': editor.isActive('orderedList') }"
        class="mr-sm"
        type="button"
        @click="editor.chain().focus().toggleOrderedList().run()"
      >
        <OrderedList />
      </button>

      <button
        :class="{ 'is-active': editor.isActive('blockquote') }"
        type="button"
        @click="editor.chain().focus().toggleBlockquote().run()"
      >
        <Blockquote />
      </button>

      <button
        v-tooltip="$t('editor.hard_break')"
        type="button"
        @click="editor.chain().focus().setHardBreak().run()"
      >
        <PageBreak />
      </button>

      <button
        v-tooltip="$t('editor.horizontal_line')"
        class="mr-sm"
        type="button"
        @click="editor.chain().focus().setHorizontalRule().run()"
      >
        <HorizontalLine />
      </button>

      <UploadButton
        class="flex"
        @upload="
          e => {
            if (e.is_local_driver) {
              addImage(`/storage/${e.path}`)
              return
            }

            addImage(e.url)
          }
        "
      >
        <button v-tooltip="$t('post.add_image')" type="button">
          <Photo />
        </button>
      </UploadButton>

      <InsertLink :editor="editor" />
    </div>

    <EditorContent :editor="editor" class="editor-classic-format max-h-96 overflow-y-auto p-xs" />
  </div>
</template>
