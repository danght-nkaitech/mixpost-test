<script setup>
import { computed, inject, provide, reactive, ref, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { cloneDeep, debounce } from 'lodash'
import useMounted from '@/Composables/useMounted'
import usePost from '@/Composables/usePost'
import usePostVersions from '@/Composables/usePostVersions'
import useNotifications from '@/Composables/useNotifications'
import usePostBroadcast from '@/Composables/usePostBroadcast'
import useOffline from '../../../Composables/useOffline'
import usePostActivity from '../../../Composables/usePostActivity'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import PostForm from '@/Components/Post/PostForm.vue'
import PostActions from '@/Components/Post/PostActions.vue'
import PostPreviewProviders from '@/Components/Post/PostPreviewProviders.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import PostStatus from '@/Components/Post/PostStatus.vue'
import Alert from '@/Components/Util/Alert.vue'
import PostErrors from '@/Components/Post/PostErrors.vue'
import EyeIcon from '@/Icons/Eye.vue'
import Tabs from '../../../Components/Navigation/Tabs.vue'
import Tab from '../../../Components/Navigation/Tab.vue'
import PostActivity from '../../../Components/PostActivity/PostActivity.vue'
import ChatBubbleBottomCenterText from '../../../Icons/ChatBubbleBottomCenterText.vue'
import SidebarIcon from '../../../Icons/Sidebar.vue'
import PostActivitySubscription from '../../../Components/PostActivity/Subscription.vue'

const props = defineProps({
  post: {
    type: [Object, null],
    default: null
  },
  scheduleAt: {
    type: Object,
    default: () => ({ date: '', time: '' })
  },
  accounts: {
    type: Array,
    default: () => []
  },
  prefill: {
    type: Object,
    default: () => ({})
  }
})

const post = props.post ? cloneDeep(props.post) : null

const workspaceCtx = inject('workspaceCtx')

const context = reactive({
  urlMeta: {},
  errors: {},
  activity: {
    default: {
      isInitialized: false,
      items: [],
      text: '',
      scrollPosition: null
    },
    thread: {
      isInitialized: false,
      items: [],
      scrollPosition: null,
      text: '',
      parent_comment: null
    }
  },
  editor: {
    label: null
  }
})

provide('postCtx', context)

const { isMounted } = useMounted()
const showPreview = ref(false)
const isLoading = ref(false)
const triedToSave = ref(false)
const hasError = ref(false)

const tab = ref('preview')

const setTab = id => {
  tab.value = id
}

const { isInHistory, isScheduleProcessing, editAllowed } = usePost()
const { versionObject } = usePostVersions()
const { connectToBroadcast } = usePostBroadcast({ props, context })
const {
  VIEW_DEFAULT: ACTIVITY_VIEW_DEFAULT,
  getInitialized: activityIsInitialized,
  isThreadView: activityIsThreadView,
  getThreadParentItem: activityThreadParentItem,
  fetchDefaultItems: activityFetchDefaultItems,
  fetchThreadItems: activityFetchThreadItems
} = usePostActivity({ context })
const { notify } = useNotifications()
const { offline } = useOffline()

const form = useForm({
  accounts: post ? post.accounts.map(account => account.id) : [],
  versions: post
    ? post.versions
    : [versionObject(0, true, props.prefill.body, [], [], props.prefill.title, props.prefill.link)],
  tags: post ? post.tags : [],
  date: post ? post.scheduled_at.date : props.scheduleAt.date,
  time: post ? post.scheduled_at.time : props.scheduleAt.time
})

const store = data => {
  router.post(route('mixpost.posts.store', { workspace: workspaceCtx.id }), data, {
    onSuccess() {
      triedToSave.value = true
      // After redirect to the edit mode, it's necessary to track the tag changes
      watchTags()
      connectToBroadcast()
    }
  })
}

const update = data => {
  isLoading.value = true

  axios
    .put(route('mixpost.posts.update', { workspace: workspaceCtx.id, post: props.post.id }), data)
    .then(response => {
      hasError.value = false

      if (response.data.status !== props.post.status) {
        router.get(route('mixpost.posts.edit', { workspace: workspaceCtx.id, post: props.post.id }))
      }
    })
    .catch(error => {
      if (error.response.status !== 422) {
        notify('error', error)
        hasError.value = true
        return
      }

      const validationErrors = error.response.data.errors

      const mustRefreshPage =
        Object.prototype.hasOwnProperty.call(validationErrors, 'in_history') ||
        Object.prototype.hasOwnProperty.call(validationErrors, 'publishing') ||
        Object.prototype.hasOwnProperty.call(validationErrors, 'user_can_not_approve')

      if (!mustRefreshPage) {
        notify('error', error)
        hasError.value = true
      }

      if (mustRefreshPage) {
        router.visit(
          route('mixpost.posts.edit', { workspace: workspaceCtx.id, post: props.post.id })
        )
      }
    })
    .finally(() => {
      triedToSave.value = true
      isLoading.value = false
    })
}

const postAccounts = computed(() => {
  if (isInHistory.value) {
    return props.post.accounts
  }

  return props.accounts.filter(account => form.accounts.includes(account.id))
})

const save = () => {
  const data = {
    accounts: form.accounts.slice(0),
    versions: form.versions.map(version => {
      return {
        account_id: version.account_id,
        is_original: version.is_original,
        content: version.content.map(item => {
          return {
            body: item.body,
            media: item.media.map(itemMedia => itemMedia.id),
            url: item.url,
            video_thumbs: item.video_thumbs
          }
        }),
        options: version.options
      }
    }),
    tags: form.tags.map(tag => tag.id),
    date: form.date,
    time: form.time
  }

  if (!props.post) {
    store(data)
  }

  if (props.post) {
    update(data)
  }
}

const watchTags = () => {
  watch(
    () => props.post.tags,
    val => {
      form.tags = val
    }
  )
}

// PostTags component deal directly with tag itself, such as renaming & changing the color,
// in this case, it's necessary to track the 'post.tags' props and update them.
// This if statement will only work in edit mode and when the page is loaded directly.
if (props.post) {
  watchTags()
}

watch(
  form,
  debounce(() => {
    if (editAllowed.value) {
      save()
    }
  }, 300)
)

watch(offline, state => {
  if (state) return
  if (!props.post) return

  if (activityIsInitialized({ view: ACTIVITY_VIEW_DEFAULT })) {
    activityFetchDefaultItems({
      workspace: workspaceCtx.id,
      post: props.post.id
    })
  }

  if (activityIsThreadView.value) {
    activityFetchThreadItems({
      workspace: workspaceCtx.id,
      post: props.post.id,
      activity: activityThreadParentItem().id
    })
  }
})
</script>
<template>
  <Head :title="$t('post.your_post')" />

  <div class="flex flex-col grow h-full overflow-y-auto">
    <div class="flex flex-row h-full overflow-y-auto">
      <div class="w-full h-full flex flex-col overflow-x-hidden overflow-y-auto">
        <div class="flex flex-col h-full">
          <PostErrors />

          <div class="row-py h-full overflow-y-auto">
            <PageHeader :title="$t('post.your_post')" class="m-container mx-auto">
              <div v-if="$page.props.post" class="flex items-center">
                <PostStatus :value="$page.props.post.status" />
                <div class="flex items-center" :class="{ 'ml-lg': triedToSave || hasError }">
                  <div
                    :class="{
                      hidden: !triedToSave,
                      'animate-ping': isLoading,
                      'bg-lime-500': !hasError,
                      'bg-red-500': hasError
                    }"
                    class="w-4 h-4 mr-xs rounded-full"
                  ></div>
                  <div v-if="!hasError && triedToSave">{{ $t('general.saved') }}</div>
                  <div v-if="hasError">{{ $t('error.error_saving') }}</div>
                </div>
              </div>
            </PageHeader>

            <div class="w-full m-container mx-auto row-px">
              <template v-if="isInHistory">
                <Alert :closeable="false" class="mb-lg">
                  {{ $t('post.posts_history_not_edited') }}
                </Alert>
              </template>

              <template v-if="isScheduleProcessing">
                <Alert :closeable="false" variant="warning" class="mb-lg">
                  {{ $t('post.post_being_published') }}
                </Alert>
              </template>

              <template v-if="post && post.trashed">
                <Alert :closeable="false" variant="warning" class="mb-lg">
                  {{ $t('post.posts_in_trash_not_be_edited') }}
                </Alert>
              </template>

              <PostForm :form="form" :accounts="$page.props.accounts" />
            </div>
          </div>
        </div>
      </div>
      <div
        :class="{
          'translate-x-0 pb-32': showPreview,
          'translate-x-full md:translate-x-0': !showPreview
        }"
        class="fixed md:relative w-full md:w-[750px] h-full overflow-x-hidden overflow-y-auto flex flex-col border-l border-gray-200 bg-stone-500 transition-transform ease-in-out duration-200"
      >
        <Teleport v-if="isMounted && form.accounts.length" to="#navRightButton">
          <SecondaryButton size="xs" class="md:hidden" @click="showPreview = !showPreview">
            <template #icon>
              <SidebarIcon />
            </template>
          </SecondaryButton>
        </Teleport>

        <div class="flex pb-md flex-col h-full">
          <div class="flex flex-col h-full">
            <div class="row-px pt-md relative border-b border-gray-200">
              <Tabs>
                <Tab :active="tab === 'preview'" @click="setTab('preview')">
                  <template #icon>
                    <EyeIcon />
                  </template>
                  {{ $t('post.preview') }}
                </Tab>
                <Tab
                  :active="tab === 'activity'"
                  :disabled="$page.props.post === null"
                  @click="setTab('activity')"
                >
                  <template #icon>
                    <ChatBubbleBottomCenterText />
                  </template>
                  {{ $t('post_activity.activity') }}
                </Tab>
              </Tabs>

              <PostActivitySubscription />
            </div>

            <div class="flex flex-col h-full overflow-y-auto">
              <template v-if="tab === 'preview'">
                <PostPreviewProviders
                  :accounts="postAccounts"
                  :versions="form.versions"
                  class="row-px row-pt"
                />
              </template>

              <template v-if="tab === 'activity' && $page.props.post !== null">
                <PostActivity :post="$page.props.post" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <PostActions :form="form" :has-available-times="$page.props.has_available_times" />
  </div>
</template>
