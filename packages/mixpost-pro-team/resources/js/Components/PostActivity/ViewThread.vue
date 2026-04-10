<script setup>
import { useI18n } from 'vue-i18n'
import { computed, inject, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import emitter from '@/Services/emitter'
import useNotifications from '../../Composables/useNotifications'
import useScrollPosition from '../../Composables/useScrollPosition'
import usePreloader from '../../Composables/usePreloader'
import ItemCommentType from './ItemCommentType.vue'
import NewComment from './NewComment.vue'
import NProgress from 'nprogress'
import Flex from '../Layout/Flex.vue'
import Avatar from '../DataDisplay/Avatar.vue'
import PureButton from '../Button/PureButton.vue'
import ArrowLeft from '../../Icons/ArrowLeft.vue'
import Preloader from '../Util/Preloader.vue'
import usePostActivity from '../../Composables/usePostActivity'

const { t: $t } = useI18n()

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
  VIEW_THREAD,
  threadParentComment,
  getItemsCount,
  getLastScrollPosition,
  setLastScrollPosition,
  destroyThread,
  getItem,
  fetchThreadItems
} = usePostActivity({ context: postCtx })
const { isLoadingPreloader, startPreloader, stopPreloader } = usePreloader({})
const listRef = ref(null)
const { scrollPosition, setScrollPosition, scrollToBottom, isNearBottom } =
  useScrollPosition(listRef)

const repliesCount = computed(() => {
  return getItemsCount({ view: VIEW_THREAD })
})

const fetchItems = async () => {
  if (!threadParentComment.value) return

  startPreloader()
  NProgress.start()

  await fetchThreadItems({
    workspace: workspaceCtx.id,
    post: props.post.id,
    activity: threadParentComment.value.id
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
      stopPreloader()
    })
}

onMounted(() => {
  const lastScrollPosition = getLastScrollPosition({ view: VIEW_THREAD })
  const itemsCount = getItemsCount({ view: VIEW_THREAD })

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
  setLastScrollPosition({ view: VIEW_THREAD, state: scrollPosition.value })
  emitter.off('postActivityCreated')
})

const close = () => destroyThread()

const updateParentCommentCountChildren = () => {
  if (!threadParentComment.value) return

  const item = getItem({ view: VIEW_DEFAULT, id: threadParentComment.value.id })

  if (item) {
    item.children_count = repliesCount.value
  }
}
</script>

<template>
  <div class="row-px py-sm border-b border-gray-200">
    <Flex>
      <PureButton class="mr-xl" @click="close">
        <ArrowLeft />
      </PureButton>

      <Flex class="items-center">
        <span class="font-medium">{{ $t('post_activity.thread_by') }}</span>

        <Flex :responsive="false">
          <Avatar
            v-if="threadParentComment.user"
            :name="threadParentComment.user.name"
            size="xs"
            class="cursor-default"
          />
          <div>{{ threadParentComment.user?.name ?? $t('post_activity.deleted_user') }}</div>
        </Flex>
      </Flex>
    </Flex>
  </div>

  <div
    ref="listRef"
    class="relative flex flex-col h-full overflow-y-scroll mixpost-scroll-style row-px row-pt pr-1 sm:pr-4! lg:pr-6! pb-md"
  >
    <Transition>
      <template v-if="isLoadingPreloader">
        <Preloader :opacity="50" />
      </template>
    </Transition>

    <ItemCommentType
      :item="threadParentComment"
      :post-id="post.id"
      :is-child="true"
      @delete="close"
    />

    <div
      v-if="repliesCount"
      class="flex items-center my-md after:content-[''] after:flex-1 after:h-[1px] after:bg-gray-200"
    >
      {{ $t('post_activity.reply_n', repliesCount) }}
    </div>

    <template v-if="postCtx.activity[VIEW_THREAD].items.length">
      <ul class="space-y-md">
        <li v-for="item in postCtx.activity[VIEW_THREAD].items" :key="item.id">
          <ItemCommentType :item="item" :post-id="post.id" :is-child="true" />
        </li>
      </ul>
    </template>
  </div>

  <NewComment
    view="thread"
    :post="post"
    :parent-id="threadParentComment.id"
    @stored="
      () => {
        updateParentCommentCountChildren()
        nextTick(() => {
          scrollToBottom()
        })
      }
    "
  />
</template>
