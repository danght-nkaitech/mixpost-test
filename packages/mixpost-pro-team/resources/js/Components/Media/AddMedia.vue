<script setup>
import { computed, inject, ref } from 'vue'
import useMedia from '@/Composables/useMedia'
import DialogModal from '@/Components/Modal/DialogModal.vue'
import Tabs from '@/Components/Navigation/Tabs.vue'
import Tab from '@/Components/Navigation/Tab.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import MediaUploads from '@/Components/Media/MediaUploads.vue'
import MediaStock from '@/Components/Media/MediaStock.vue'
import MediaGifs from '@/Components/Media/MediaGifs.vue'
import MediaNewDesign from '@/Components/Media/MediaNewDesign.vue'
import Preloader from '@/Components/Util/Preloader.vue'
import XIcon from '@/Icons/X.vue'

const workspaceCtx = inject('workspaceCtx')

const props = defineProps({
  maxSelection: {
    type: Number,
    default: 1
  },
  showImmediate: {
    type: Boolean,
    default: false
  },
  disableTrigger: {
    type: Boolean,
    default: false
  },
  maxSelectedItems: {
    type: Number,
    default: -1 //infinite
  },
  mimeTypes: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['insert', 'close'])

const show = ref(false)

const { activeTab, tabs, isDownloading, downloadExternal, getMediaCrediting } = useMedia(
  'mixpost.media.fetchStock',
  { workspace: workspaceCtx.id },
  props.maxSelectedItems,
  props.mimeTypes
)

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

const sourceParams = () => {
  const params = {
    maxSelectedItems: props.maxSelectedItems
  }
  if ([sources.uploads, sources.new_design].includes(source.value)) {
    params.mimeTypes = props.mimeTypes
  }
  if (sources.uploads === source.value) {
    params.selectedItems = selectedItems.value
  }
  return params
}

const selectedItems = computed(() => {
  return sourceProperties?.value?.selected ?? []
})

const deselectAll = () => {
  sourceProperties?.value?.deselectAll?.()
}

const close = () => {
  deselectAll()
  show.value = false
  activeTab.value = 'uploads'
  if (props.showImmediate) {
    emit('close')
  }
}

const insert = () => {
  const toDownload = !['uploads', 'new_design'].includes(activeTab.value)

  if (toDownload) {
    // Download external media files
    downloadExternal(
      selectedItems.value.map(item => {
        const { id, url, source, author, download_data } = item
        return { id, url, source, author, download_data }
      }),
      response => {
        emit('insert', {
          items: response.data,
          crediting: getMediaCrediting(response.data)
        })

        close()
      }
    )
  }

  if (!toDownload) {
    emit('insert', {
      items: selectedItems.value,
      crediting: getMediaCrediting(selectedItems.value)
    })

    close()
  }
}
</script>
<template>
  <template v-if="!props.disableTrigger">
    <div @click="show = !show">
      <slot />
    </div>
  </template>

  <DialogModal
    :show="show || props.showImmediate"
    max-width="2xl"
    :closeable="true"
    :scrollable-body="true"
    @close="close"
  >
    <template #header>
      {{ $t('media.add_media') }}
    </template>

    <template #body>
      <Preloader v-if="isDownloading" :opacity="75">
        {{ $t('media.downloading') }}
      </Preloader>

      <Tabs>
        <template v-for="tab in tabs" :key="tab">
          <Tab :active="activeTab === tab" @click="activeTab = tab">{{ $t(`media.${tab}`) }}</Tab>
        </template>
      </Tabs>

      <div class="mt-lg">
        <component
          :is="source"
          ref="sourceProperties"
          :adaptive="false"
          v-bind="sourceParams()"
          @close="close"
          @insert="insert"
        />
      </div>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs rtl:mr-0 rtl:ml-xs" @click="close">{{
        $t('general.cancel')
      }}</SecondaryButton>

      <template v-if="props.maxSelectedItems === 1">
        <PrimaryButton @click="insert">
          {{ $t('general.insert') }}
        </PrimaryButton>
      </template>
      <template v-else-if="selectedItems.length">
        <SecondaryButton
          v-tooltip.top="$t('general.dismiss')"
          size="md"
          class="mr-xs rtl:mr-0 rtl:ml-xs"
          @click="deselectAll"
        >
          <template #icon>
            <XIcon />
          </template>
        </SecondaryButton>

        <PrimaryButton @click="insert">
          {{ $t('general.insert') }} {{ selectedItems.length }} {{ $t('general.items') }}
        </PrimaryButton>
      </template>
    </template>
  </DialogModal>
</template>
