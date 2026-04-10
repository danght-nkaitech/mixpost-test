<script setup>
import { computed, inject, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import emitter from '@/Services/emitter'
import usePostVersions from '@/Composables/usePostVersions'
import useMedia from '@/Composables/useMedia'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Tabs from '@/Components/Navigation/Tabs.vue'
import Tab from '@/Components/Navigation/Tab.vue'
import MediaUploads from '@/Components/Media/MediaUploads.vue'
import MediaStock from '@/Components/Media/MediaStock.vue'
import MediaGifs from '@/Components/Media/MediaGifs.vue'
import SelectableBar from '@/Components/DataDisplay/SelectableBar.vue'
import PureDangerButton from '@/Components/Button/PureDangerButton.vue'
import DangerButton from '@/Components/Button/DangerButton.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import Panel from '@/Components/Surface/Panel.vue'
import ConfirmationModal from '@/Components/Modal/ConfirmationModal.vue'
import TrashIcon from '@/Icons/Trash.vue'
import PlusIcon from '@/Icons/Plus.vue'
import MediaNewDesign from '../../Components/Media/MediaNewDesign.vue'

const workspaceCtx = inject('workspaceCtx')

const {
  activeTab,
  tabs,
  isDownloading,
  isDeleting,
  downloadExternal,
  deletePermanently,
  getMediaCrediting
} = useMedia('mixpost.media.fetchStock', { workspace: workspaceCtx.id })

const sources = {
  uploads: MediaUploads,
  stock: MediaStock,
  gifs: MediaGifs,
  new_design: MediaNewDesign
}

const sourceProperties = ref()

const source = computed(() => {
  return sources[activeTab.value]
})

const selectedItems = computed(() => {
  return sourceProperties?.value?.selected ?? []
})

const deselectAll = () => {
  sourceProperties.value.deselectAll?.()
}

const use = () => {
  const toDownload = !['uploads', 'new_design'].includes(activeTab.value)

  if (toDownload) {
    downloadExternal(
      selectedItems.value.map(item => {
        const { id, url, source, author, download_data } = item
        return { id, url, source, author, download_data }
      }),
      response => {
        createPost(response.data)
      }
    )
  }

  if (!toDownload) {
    createPost(selectedItems.value)
  }
}

const { versionObject } = usePostVersions()

const createPost = mediaCollection => {
  router.post(route('mixpost.posts.store', { workspace: workspaceCtx.id }), {
    versions: [
      versionObject(
        0,
        true,
        getMediaCrediting(mediaCollection),
        mediaCollection.map(item => item.id)
      )
    ]
  })
}

const confirmationDeletion = ref(false)

const deleteSelectedItems = () => {
  const items = selectedItems.value.map(item => item.id)

  deletePermanently(items, () => {
    deselectAll()
    sourceProperties.value.removeItems(items)
    emitter.emit('mediaDelete', items)
    confirmationDeletion.value = false
  })
}

const selectedMedia = ref()

const sourceParams = computed(() => {
  return sources.uploads === source.value && selectedMedia.value
    ? { selectedItems: [selectedMedia.value] }
    : {}
})
</script>
<template>
  <Head :title="$t('media.media_library')" />

  <div class="w-full mx-auto row-py mb-2xl">
    <PageHeader :title="$t('media.media_library')" />

    <div class="w-full row-px">
      <Tabs>
        <template v-for="tab in tabs" :key="tab">
          <Tab :active="activeTab === tab" @click="activeTab = tab">{{ $t(`media.${tab}`) }}</Tab>
        </template>
      </Tabs>
    </div>

    <div class="w-full row-px mt-lg">
      <Panel>
        <component
          :is="source"
          v-bind="sourceParams"
          ref="sourceProperties"
          :columns="5"
          v-on="
            source?.emits?.includes('select-media-in-media-library')
              ? {
                  'select-media-in-media-library': media => {
                    activeTab = 'uploads'
                    selectedMedia = media
                  }
                }
              : {}
          "
        />

        <SelectableBar :count="selectedItems.length" @close="deselectAll()">
          <SecondaryButton
            :is-loading="isDownloading"
            :disabled="isDownloading"
            class="mr-sm rtl:mr-0 rtl:ml-sm"
            size="xs"
            @click="use"
          >
            <template #icon>
              <PlusIcon />
            </template>

            {{ $t('media.create_post') }}
          </SecondaryButton>

          <template v-if="activeTab === 'uploads'">
            <PureDangerButton v-tooltip="$t('general.delete')" @click="confirmationDeletion = true">
              <TrashIcon />
            </PureDangerButton>
          </template>
        </SelectableBar>
      </Panel>
    </div>
  </div>

  <ConfirmationModal
    :show="confirmationDeletion"
    variant="danger"
    @close="confirmationDeletion = false"
  >
    <template #header>
      {{ $t('media.delete_media') }}
    </template>
    <template #body>
      {{ $t('media.do_you_want_delete') }}
    </template>
    <template #footer>
      <SecondaryButton class="mr-xs rtl:mr-0 rtl:ml-xs" @click="confirmationDeletion = false">
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <DangerButton :is-loading="isDeleting" :disabled="isDeleting" @click="deleteSelectedItems">
        {{ $t('general.delete') }}
      </DangerButton>
    </template>
  </ConfirmationModal>
</template>
