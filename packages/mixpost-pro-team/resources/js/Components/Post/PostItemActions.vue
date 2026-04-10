<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage, router } from '@inertiajs/vue3'
import emitter from '@/Services/emitter'
import useNotifications from '@/Composables/useNotifications'
import PureButtonLink from '@/Components/Button/PureButtonLink.vue'
import PureButton from '@/Components/Button/PureButton.vue'
import EllipsisVerticalIcon from '@/Icons/EllipsisVertical.vue'
import Dropdown from '@/Components/Dropdown/Dropdown.vue'
import DropdownItem from '@/Components/Dropdown/DropdownItem.vue'
import PencilSquareIcon from '@/Icons/PencilSquare.vue'
import DuplicateIcon from '@/Icons/Duplicate.vue'
import TrashIcon from '@/Icons/Trash.vue'
import ArrowUturnLeft from '../../Icons/ArrowUturnLeft.vue'
import useWorkspace from '../../Composables/useWorkspace.js'
import Eye from '../../Icons/Eye.vue'
import PostDeletionConfirmationModal from '@/Components/Post/PostDeletionConfirmationModal.vue'
import DropdownGroupTitle from '@/Components/Dropdown/DropdownGroupTitle.vue'
import MenuDelimiter from '@/Components/Sidebar/MenuDelimiter.vue'

const { t: $t } = useI18n()

const workspaceCtx = inject('workspaceCtx')

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  trashed: {
    type: Boolean,
    default: false
  },
  supportPostDeletion: {
    type: Object
  }
})

const emit = defineEmits(['onDelete'])

const confirmationDeletion = ref(false)

const filterStatus = computed(() => {
  const pageProps = usePage().props

  return Object.prototype.hasOwnProperty.call(pageProps, 'filter') ? pageProps.filter.status : null
})

const { notify } = useNotifications()
const { isWorkspaceEditorRole } = useWorkspace()

const deletePost = ({ deleteMode }) => {
  return new Promise((resolve, reject) => {
    router.delete(route('mixpost.posts.delete', { workspace: workspaceCtx.id }), {
      data: {
        posts: [props.item.id],
        status: filterStatus.value,
        delete_mode: deleteMode
      },
      onSuccess() {
        confirmationDeletion.value = false
        if (['app_only', 'app_and_social'].includes(deleteMode)) {
          notify(
            'success',
            filterStatus.value === 'trash'
              ? $t('post.post_deleted_permanently')
              : $t('post.post_moved_to_trash')
          )
        }
        if (['social_only'].includes(deleteMode)) {
          notify('success', $t('post.post_deleted_from_social_platforms'))
        }
        emit('onDelete')
        emitter.emit('postDelete', props.item.id)

        resolve()
      },
      onError() {
        reject()
      }
    })
  })
}

const duplicate = () => {
  router.post(
    route('mixpost.posts.duplicate', { workspace: workspaceCtx.id, post: props.item.id }),
    {},
    {
      onSuccess() {
        notify('success', $t('post.post_duplicated'))
      }
    }
  )
}

const restore = () => {
  router.post(
    route('mixpost.posts.restore', { workspace: workspaceCtx.id, post: props.item.id }),
    {},
    {
      onSuccess() {
        notify('success', $t('post.post_restored'))
      }
    }
  )
}
// console.log('single item', props.item);
</script>
<template>
  <div>
    <div class="flex flex-row items-center gap-xs">
      <PureButtonLink
        v-tooltip="$t(!isWorkspaceEditorRole ? 'general.view' : 'general.edit')"
        :href="route('mixpost.posts.edit', { workspace: workspaceCtx.id, post: item.id })"
      >
        <template v-if="!isWorkspaceEditorRole">
          <Eye />
        </template>
        <template v-else>
          <PencilSquareIcon />
        </template>
      </PureButtonLink>

      <template v-if="isWorkspaceEditorRole">
        <Dropdown placement="bottom-end">
          <template #trigger>
            <PureButton class="mt-1">
              <EllipsisVerticalIcon />
            </PureButton>
          </template>

          <template #content>
            <template v-if="trashed">
              <DropdownItem as="button" @click="restore">
                <template #icon>
                  <ArrowUturnLeft />
                </template>
                {{ $t('general.restore') }}
              </DropdownItem>
            </template>

            <DropdownItem as="button" @click="duplicate">
              <template #icon>
                <DuplicateIcon />
              </template>

              {{ $t('general.duplicate') }}
            </DropdownItem>

            <template v-if="$page.props.mixpost.features.api_access_tokens">
              <MenuDelimiter />

              <DropdownGroupTitle>{{ $t('system.usage_api') }}</DropdownGroupTitle>

              <DropdownItem as="button" :copyable="true" :copy-text="item.id">
                {{ $t('system.copy_uuid') }}
              </DropdownItem>

              <MenuDelimiter />
            </template>

            <DropdownItem as="button" @click="confirmationDeletion = true">
              <template #icon>
                <TrashIcon class="text-red-500" />
              </template>

              {{ $t('general.delete') }}
            </DropdownItem>
          </template>
        </Dropdown>
      </template>
    </div>
    <PostDeletionConfirmationModal
      :posts="[item]"
      :support-post-deletion="supportPostDeletion"
      :delete-handler="deletePost"
      :show="confirmationDeletion"
      @close="confirmationDeletion = false"
    />
  </div>
</template>
