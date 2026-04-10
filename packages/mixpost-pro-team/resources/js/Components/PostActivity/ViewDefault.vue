<script setup>
import { inject, nextTick, onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'
import emitter from '@/Services/emitter'
import useNotifications from '../../Composables/useNotifications'
import useScrollPosition from '../../Composables/useScrollPosition'
import ItemCommentType from './ItemCommentType.vue'
import ItemCreatedType from './ItemCreatedType.vue'
import ItemUpdatedScheduleTimeType from './ItemUpdatedScheduleTimeType.vue'
import ItemScheduledType from './ItemScheduledType.vue'
import ItemPublishedType from './ItemPublishedType.vue'
import ItemSetDraftType from './ItemSetDraftType.vue'
import ItemScheduleProcessingType from './ItemScheduleProcessingType.vue'
import ItemPublishedFailedType from './ItemPublishedFailedType.vue'
import NewComment from './NewComment.vue'
import NProgress from 'nprogress'
import usePostActivity from '../../Composables/usePostActivity'
import ItemDeletedFromSocialPlatformType from './ItemDeletedFromSocialPlatformType.vue'

const props = defineProps({
  post: {
    required: true,
    type: Object
  }
})

const workspaceCtx = inject('workspaceCtx')
const postCtx = inject('postCtx')

const { notify } = useNotifications()
const {
  VIEW_DEFAULT,
  getItemsCount,
  getLastScrollPosition,
  setLastScrollPosition,
  fetchDefaultItems
} = usePostActivity({ context: postCtx })
const listRef = ref(null)
const { scrollPosition, setScrollPosition, scrollToBottom, isNearBottom } =
  useScrollPosition(listRef)
const itemComponentType = shallowRef({
  COMMENT: ItemCommentType,
  CREATED: ItemCreatedType,
  SET_DRAFT: ItemSetDraftType,
  UPDATED_SCHEDULE_TIME: ItemUpdatedScheduleTimeType,
  SCHEDULED: ItemScheduledType,
  SCHEDULE_PROCESSING: ItemScheduleProcessingType,
  PUBLISHED: ItemPublishedType,
  PUBLISHED_FAILED: ItemPublishedFailedType,
  DELETED_FROM_SOCIAL_PLATFORMS: ItemDeletedFromSocialPlatformType
})

const fetchItems = async () => {
  NProgress.start()

  await fetchDefaultItems({
    workspace: workspaceCtx.id,
    post: props.post.id
  })
    .then(() => {
      setTimeout(() => {
        nextTick(scrollToBottom)
      }, 0)
    })
    .catch(error => {
      notify('error', error)
    })
    .finally(() => {
      NProgress.done()
    })
}

onMounted(() => {
  const lastScrollPosition = getLastScrollPosition({ view: VIEW_DEFAULT })
  const itemsCount = getItemsCount({ view: VIEW_DEFAULT })

  emitter.on('postActivityCreated', () => {
    if (isNearBottom()) {
      nextTick(scrollToBottom)
    }
  })

  if (!itemsCount) {
    fetchItems()
  }

  if (lastScrollPosition !== null) {
    setTimeout(() => {
      nextTick(() => setScrollPosition(lastScrollPosition))
    }, 0)
  }

  if (lastScrollPosition === null && itemsCount) {
    setTimeout(() => {
      nextTick(scrollToBottom)
    }, 0)
  }
})

onBeforeUnmount(() => {
  setLastScrollPosition({ view: VIEW_DEFAULT, state: scrollPosition.value })
  emitter.off('postActivityCreated')
})
</script>
<template>
  <div
    ref="listRef"
    class="flex flex-col h-full overflow-y-scroll mixpost-scroll-style row-px row-pt pr-1 sm:pr-4! lg:pr-6! pb-md"
  >
    <template v-if="postCtx.activity[VIEW_DEFAULT].items.length">
      <ul class="space-y-md">
        <li v-for="item in postCtx.activity[VIEW_DEFAULT].items" :key="item.id">
          <component :is="itemComponentType[item.type]" :item="item" :post-id="post.id" />
        </li>
      </ul>
    </template>
  </div>

  <NewComment
    view="default"
    :post="post"
    @stored="
      () => {
        nextTick(() => {
          scrollToBottom()
        })
      }
    "
  />
</template>
