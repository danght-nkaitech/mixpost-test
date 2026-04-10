<script setup>
import { inject, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import Flex from '../Layout/Flex.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import { useAPIForm } from '../../Composables/useAPIForm'
import useNotifications from '../../Composables/useNotifications'
import Editor from './Editor.vue'
import EditorToolbar from './EditorToolbar.vue'
import usePostActivity from '../../Composables/usePostActivity'

const { t: $t } = useI18n()

const props = defineProps({
  post: {
    required: true,
    type: [Object, null]
  },
  parentId: {
    required: false,
    type: [String, null]
  },
  view: {
    required: false,
    type: String,
    default: 'default' // default, thread
  }
})

const emit = defineEmits(['stored'])

const editorRef = ref(null)
const routePrefix = inject('routePrefix')
const workspaceCtx = inject('workspaceCtx')
const postCtx = inject('postCtx')

const { notify } = useNotifications()
const { addItem, setNewCommentText, getDraftCommentText } = usePostActivity({ context: postCtx })

const focus = ref(false)
const form = useAPIForm({
  text: '',
  parent_id: props.parentId
})

const clearText = () => {
  if (editorRef.value && editorRef.value.editor) {
    editorRef.value.editor.commands.clearContent()
  }

  setText('')
}

const setText = value => {
  form.text = value
  setNewCommentText({ view: props.view, text: value })
}

const submit = () => {
  if (!form.text) {
    return
  }

  if (!props.post) {
    return
  }

  form.post(
    route(`${routePrefix}.posts.comments.store`, {
      workspace: workspaceCtx.id,
      post: props.post.id
    }),
    {
      onSuccess: response => {
        addItem({ view: props.view, activity: response })
        emit('stored', response)
        clearText()
      },
      onError: error => {
        notify('error', error)
      }
    }
  )
}

onMounted(() => {
  const text = getDraftCommentText({ view: props.view })

  if (text) {
    form.text = text

    if (editorRef.value && editorRef.value.editor) {
      editorRef.value.editor.commands.setContent(text)
      editorRef.value.editor.commands.focus('end')
    }
  }
})
</script>
<template>
  <div class="row-px">
    <div
      :class="{ 'border-primary-200 ring-3 ring-primary-200/50': focus }"
      class="border bg-white border-gray-200 rounded-lg p-md pb-xs text-base transition-colors ease-in-out duration-200"
    >
      <Editor
        ref="editorRef"
        v-model="form.text"
        @update:model-value="setText"
        @enter="submit"
        @focus="focus = true"
        @blur="focus = false"
      />
      <Flex
        :responsive="false"
        class="relative justify-between border-t border-gray-200 pt-xs mt-md items-center"
      >
        <template v-if="editorRef && editorRef.editor">
          <EditorToolbar :editor="editorRef.editor" :text="form.text" />
        </template>
        <PrimaryButton :is-loading="form.processing" :disabled="form.text === ''" @click="submit">
          {{ $t('post_activity.send') }}
        </PrimaryButton>
      </Flex>
    </div>
  </div>
</template>
