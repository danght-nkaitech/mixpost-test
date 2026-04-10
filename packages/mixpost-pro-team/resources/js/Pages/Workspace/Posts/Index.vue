<script setup>
import { inject, onMounted, onUnmounted, provide, reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, router } from '@inertiajs/vue3'
import emitter from '@/Services/emitter'
import useNotifications from '@/Composables/useNotifications'
import { cloneDeep, pickBy, throttle } from 'lodash'
import useSelectable from '@/Composables/useSelectable'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import PostsFilter from '@/Components/Post/PostsFilter.vue'
import Tabs from '@/Components/Navigation/Tabs.vue'
import Tab from '@/Components/Navigation/Tab.vue'
import Panel from '@/Components/Surface/Panel.vue'
import Checkbox from '@/Components/Form/Checkbox.vue'
import Table from '@/Components/DataDisplay/Table.vue'
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import PureDangerButton from '@/Components/Button/PureDangerButton.vue'
import PostItem from '@/Components/Post/PostItem.vue'
import SelectableBar from '@/Components/DataDisplay/SelectableBar.vue'
import Pagination from '@/Components/Navigation/Pagination.vue'
import NoResult from '@/Components/Util/NoResult.vue'
import TrashIcon from '@/Icons/Trash.vue'
import PostDeletionConfirmationModal from '@/Components/Post/PostDeletionConfirmationModal.vue'

const { t: $t } = useI18n()

const props = defineProps({
  filter: {
    type: Object,
    default: () => ({})
  },
  posts: {
    type: Object
  },
  hasFailedPosts: {
    type: Boolean,
    default: false
  },
  hasNeedsApprovalPosts: {
    type: Boolean,
    default: false
  },
  supportPostDeletion: {
    type: Object
  }
})

const filter = ref({
  keyword: props.filter.keyword,
  status: props.filter.status,
  tags: props.filter.tags,
  accounts: props.filter.accounts
})

const postContext = reactive({
  urlMeta: {}
})

provide('postCtx', postContext)

const {
  selectedRecords,
  putPageRecords,
  toggleSelectRecordsOnPage,
  deselectRecord,
  deselectAllRecords
} = useSelectable()

const workspaceCtx = inject('workspaceCtx')

const itemsId = () => {
  return props.posts.data.map(item => item.id)
}

onMounted(() => {
  putPageRecords(itemsId())

  emitter.on('postDelete', id => {
    deselectRecord(id)
  })
})

onUnmounted(() => {
  emitter.off('postDelete')
})

watch(
  () => cloneDeep(filter.value),
  throttle(() => {
    router.get(route('mixpost.posts.index', { workspace: workspaceCtx.id }), pickBy(filter.value), {
      preserveState: true,
      only: ['posts', 'filter']
    })
  }, 300)
)

watch(
  () => props.posts.data,
  () => {
    putPageRecords(itemsId())
  }
)

const { notify } = useNotifications()
const confirmationDeletion = ref(false)

const deletePosts = ({ deleteMode }) => {
  return new Promise((resolve, reject) => {
    router.delete(route('mixpost.posts.delete', { workspace: workspaceCtx.id }), {
      data: {
        posts: selectedRecords.value,
        status: filter.value.status,
        delete_mode: deleteMode
      },
      onSuccess() {
        deselectAllRecords()
        if (['app_only', 'app_and_social'].includes(deleteMode)) {
          notify(
            'success',
            props.filter.status === 'trash' ? $t('post.posts_deleted') : $t('post.posts_to_trash')
          )
        }
        if (['social_only'].includes(deleteMode)) {
          notify('success', $t('post.posts_deleted_from_social_platforms'))
        }
        confirmationDeletion.value = false

        resolve()
      },
      onError() {
        reject()
      }
    })
  })
}
</script>
<template>
  <Head :title="$t('post.posts')" />

  <div class="row-py">
    <PageHeader :title="$t('post.posts')">
      <PostsFilter v-model="filter" class="md:ml-xs md:rtl:ml-0 md:rtl:mr-xs" />
    </PageHeader>

    <div class="w-full row-px">
      <Tabs>
        <Tab :active="!$page.props.filter.status" @click="filter.status = null">{{
          $t('general.all')
        }}</Tab>
        <Tab :active="$page.props.filter.status === 'draft'" @click="filter.status = 'draft'">
          {{ $t('post.drafts') }}
        </Tab>
        <template v-if="hasNeedsApprovalPosts">
          <Tab
            :active="$page.props.filter.status === 'needs_approval'"
            @click="filter.status = 'needs_approval'"
          >
            {{ $t('post.needs_approval') }}
          </Tab>
        </template>
        <Tab
          :active="$page.props.filter.status === 'scheduled'"
          @click="filter.status = 'scheduled'"
        >
          {{ $t('post.scheduled') }}
        </Tab>
        <Tab
          :active="$page.props.filter.status === 'published'"
          @click="filter.status = 'published'"
        >
          {{ $t('post.published') }}
        </Tab>
        <template v-if="hasFailedPosts">
          <Tab
            :active="$page.props.filter.status === 'failed'"
            class="text-red-500"
            @click="filter.status = 'failed'"
            >{{ $t('post.failed') }}
          </Tab>
        </template>
        <Tab :active="$page.props.filter.status === 'trash'" @click="filter.status = 'trash'">
          {{ $t('general.trash') }}
        </Tab>
      </Tabs>
    </div>

    <div class="w-full row-px mt-lg">
      <SelectableBar :count="selectedRecords.length" @close="deselectAllRecords">
        <PureDangerButton v-tooltip="$t('general.delete')" @click="confirmationDeletion = true">
          <TrashIcon />
        </PureDangerButton>
      </SelectableBar>

      <Panel :with-padding="false">
        <Table>
          <template #head>
            <TableRow>
              <TableCell component="th" scope="col" class="w-10">
                <Checkbox
                  v-model:checked="toggleSelectRecordsOnPage"
                  :disabled="!posts.meta.total"
                />
              </TableCell>
              <TableCell component="th" scope="col" class="w-44">{{ $t('post.status') }}</TableCell>
              <TableCell component="th" scope="col" class="pl-0! text-left">
                {{ $t('post.content') }}
              </TableCell>
              <TableCell component="th" scope="col" class="w-48">{{ $t('post.media') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('post.labels') }}</TableCell>
              <TableCell component="th" scope="col">{{ $t('post.accounts') }}</TableCell>
              <TableCell component="th" scope="col" />
            </TableRow>
          </template>
          <template #body>
            <template v-for="item in posts.data" :key="item.id">
              <PostItem
                :item="item"
                :filter="posts.filter"
                :support-post-deletion="supportPostDeletion"
                @on-delete="
                  () => {
                    deselectRecord(item.id)
                  }
                "
              >
                <template #checkbox>
                  <Checkbox v-model:checked="selectedRecords" :value="item.id" />
                </template>
              </PostItem>
            </template>
          </template>
        </Table>

        <NoResult v-if="!posts.meta.total" :with-padding="true">{{
          $t('post.no_posts_found')
        }}</NoResult>
      </Panel>

      <div v-if="posts.meta.links.length > 3" class="mt-lg">
        <Pagination :meta="posts.meta" :links="posts.links" />
      </div>
    </div>
  </div>
  <PostDeletionConfirmationModal
    :posts="posts.data.filter(post => selectedRecords.includes(post.id))"
    :support-post-deletion="supportPostDeletion"
    :delete-handler="deletePosts"
    :show="confirmationDeletion"
    @close="confirmationDeletion = false"
  />
</template>
