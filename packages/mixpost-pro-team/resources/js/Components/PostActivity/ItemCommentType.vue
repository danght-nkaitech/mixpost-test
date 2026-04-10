<script setup>
import { inject, ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAPIForm } from '../../Composables/useAPIForm'
import useAuth from '../../Composables/useAuth'
import useNotifications from '../../Composables/useNotifications'
import Flex from '../Layout/Flex.vue'
import Avatar from '../DataDisplay/Avatar.vue'
import CreateAt from './CreatedAt.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import DropdownButton from '../Dropdown/DropdownButton.vue'
import DropdownItem from '../Dropdown/DropdownItem.vue'
import Editor from './Editor.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import EditorToolbar from './EditorToolbar.vue'
import EmojiPicker from '@/Components/Package/EmojiPicker.vue'
import PureButton from '../Button/PureButton.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import Trash from '../../Icons/Trash.vue'
import useWorkspace from '../../Composables/useWorkspace'
import usePostActivity from '../../Composables/usePostActivity'

const { t: $t } = useI18n()

const props = defineProps({
  item: {
    required: true,
    type: Object
  },
  postId: {
    required: true,
    type: String
  },
  isChild: {
    required: false,
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['delete'])

const confirmation = inject('confirmation')
const routePrefix = inject('routePrefix')
const workspaceCtx = inject('workspaceCtx')
const postCtx = inject('postCtx')

const editorRef = ref(null)

const { user } = useAuth()
const { isWorkspaceAdminRole } = useWorkspace()
const { notify } = useNotifications()
const {
  VIEW_DEFAULT,
  VIEW_THREAD,
  setThreadParentComment,
  removeItem: removeActivityItem
} = usePostActivity({ context: postCtx })

const isEditorReady = computed(() => editorRef.value && editorRef.value.editor)

const edit = ref(false)
const editForm = useAPIForm({
  text: props.item.text
})

const openEditForm = () => {
  editForm.text = props.item.text
  edit.value = true

  if (isEditorReady.value) {
    editorRef.value.editor.options.editable = true
    editorRef.value.editor.commands.focus('end')
  }
}

const closeEditForm = () => {
  edit.value = false

  if (isEditorReady.value) {
    editorRef.value.editor.options.editable = false
  }
}

const setText = text => {
  editForm.text = text

  if (isEditorReady.value) {
    editorRef.value.editor.commands.setContent(props.item.text)
  }
}

const resetEditForm = () => {
  setText(props.item.text)
}

const renderEmoji = reaction => {
  const codePoints = reaction.split('-').map(code => `0x${code}`)
  return String.fromCodePoint(...codePoints)
}

const updateComment = () => {
  if (!editForm.text) {
    return
  }

  editForm.put(
    route(`${routePrefix}.posts.comments.update`, {
      workspace: workspaceCtx.id,
      post: props.postId,
      activity: props.item.id
    }),
    {
      onSuccess: response => {
        if (!response) {
          notify('error', $t('error.something_wrong'))
          return
        }

        if (response) {
          closeEditForm()
          props.item.text = editForm.text
        }
      },
      onError: error => notify('error', error)
    }
  )
}

const deleteComment = () => {
  confirmation()
    .title($t('post_activity.delete_comment'))
    .description($t('post_activity.delete_comment_confirm'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      editForm.delete(
        route(`${routePrefix}.posts.comments.delete`, {
          workspace: workspaceCtx.id,
          post: props.postId,
          activity: props.item.id
        }),
        {
          onSuccess: () => {
            dialog.reset()

            if (!props.item.is_child) {
              removeActivityItem({ view: VIEW_DEFAULT, id: props.item.id })
            }

            if (props.item.is_child) {
              removeActivityItem({ view: VIEW_THREAD, id: props.item.id })
            }

            emit('delete')
          },
          onError: error => {
            dialog.reset()
            notify('error', error)
          }
        }
      )
    })
    .show()
}

const react = reactionUnified => {
  axios
    .post(
      route(`${routePrefix}.posts.comments.react`, {
        workspace: workspaceCtx.id,
        post: props.postId,
        activity: props.item.id
      }),
      {
        reaction: reactionUnified
      }
    )
    .then(function (response) {
      const index = props.item.reactions.findIndex(
        reaction => reaction.reaction === response.data.reaction
      )

      if (response.data.toggle === 'CREATED') {
        if (index > -1) {
          props.item.reactions[index].count++
          props.item.reactions[index].users.push(user.value)
        } else {
          props.item.reactions.push({
            reaction: response.data.reaction,
            count: 1,
            users: [user.value]
          })
        }
      }

      if (response.data.toggle === 'DELETED') {
        if (props.item.reactions[index].count > 1) {
          props.item.reactions[index].count--
          props.item.reactions[index].users = props.item.reactions[index].users.filter(
            u => u.id !== user.value.id
          )
        } else {
          props.item.reactions.splice(index, 1)
        }
      }
    })
    .catch(error => {
      notify('error', error)
    })
}

const openThread = () => setThreadParentComment(props.item)

watch(() => props.item.text, setText)
</script>
<template>
  <div class="group relative border bg-white border-gray-200 rounded-lg p-md">
    <Flex :responsive="false" class="justify-between">
      <Flex :responsive="false">
        <Avatar v-if="item.user" :name="item.user.name" size="sm" class="cursor-default" />
        <div>{{ item.user?.name ?? $t('post_activity.deleted_user') }}</div>
      </Flex>
      <div>
        <CreateAt
          class="visible"
          :class="{
            'group-hover:invisible': item.user && (item.user.id === user.id || isWorkspaceAdminRole)
          }"
          >{{ item.timestamps.localized.created_at }}</CreateAt
        >
        <template v-if="item.user && (item.user.id === user.id || isWorkspaceAdminRole)">
          <div class="absolute top-0 right-0 mt-sm mr-sm invisible group-hover:visible">
            <Dropdown width-classes="w-36" placement="bottom-end">
              <template #trigger>
                <DropdownButton />
              </template>

              <template #content>
                <template v-if="item.user.id === user.id">
                  <DropdownItem as="button" size="xs" @click="openEditForm">
                    <template #icon>
                      <PencilSquare />
                    </template>
                    {{ $t('general.edit') }}
                  </DropdownItem>
                </template>

                <template v-if="item.user.id === user.id || isWorkspaceAdminRole">
                  <DropdownItem as="button" size="xs" @click="deleteComment">
                    <template #icon>
                      <Trash class="text-red-500" />
                    </template>
                    {{ $t('general.delete') }}
                  </DropdownItem>
                </template>
              </template>
            </Dropdown>
          </div>
        </template>
      </div>
    </Flex>

    <div class="mt-xs">
      <Editor ref="editorRef" v-model="editForm.text" :editable="false" @enter="updateComment" />
    </div>

    <Flex
      :responsive="false"
      class="group/toolbar border-t border-gray-200 mt-xs pt-md items-start justify-between"
    >
      <template v-if="!edit">
        <Flex :wrap="true">
          <template v-if="item.reactions.length">
            <template v-for="(reaction, index) in item.reactions" :key="index">
              <button
                v-tooltip="reaction.users.map(u => (u.id === user.id ? 'Me' : u.name)).join(', ')"
                :class="{
                  'border-primary-500 bg-primary-100 text-primary-500 hover:bg-primary-200':
                    reaction.users.some(u => u.id === user.id)
                }"
                class="border border-gray-200 rounded-xl px-[6px] hover:bg-primary-100 transition-colors duration-200"
                @click="react(reaction.reaction)"
              >
                <span class="mr-1">{{ renderEmoji(reaction.reaction) }}</span
                ><span class="text-sm">{{ reaction.count }}</span>
              </button>
            </template>
          </template>
          <div :class="{ 'hidden group-hover/toolbar:block': item.reactions.length }">
            <!--TODO: Fix close on select when searching...-->
            <EmojiPicker
              :tooltip="$t('editor.add_reaction')"
              :close-on-select="true"
              @selected="
                emoji => {
                  react(emoji.unified)
                }
              "
            />
          </div>
        </Flex>

        <template v-if="!isChild">
          <PureButton @click="openThread">
            <span v-if="item.children_count">{{
              $t('post_activity.reply_n', item.children_count)
            }}</span>
            <span v-else>{{ $t('post_activity.reply') }}</span>
          </PureButton>
        </template>
      </template>

      <template v-if="edit">
        <template v-if="editorRef && editorRef.editor">
          <EditorToolbar :editor="editorRef.editor" :text="editForm.text" />
        </template>
        <Flex>
          <SecondaryButton
            :disabled="editForm.processing"
            size="md"
            @click="
              () => {
                resetEditForm()
                closeEditForm()
              }
            "
          >
            {{ $t('general.cancel') }}
          </SecondaryButton>
          <PrimaryButton
            :is-loading="editForm.processing"
            :disabled="editForm.processing"
            size="md"
            @click="updateComment"
          >
            {{ $t('general.save') }}
          </PrimaryButton>
        </Flex>
      </template>
    </Flex>
  </div>
</template>
