<script setup>
import { inject, onMounted } from 'vue'
import useMedia from '@/Composables/useMedia'
import UploadMedia from '@/Components/Media/UploadMedia.vue'
import MediaSelectable from '@/Components/Media/MediaSelectable.vue'
import MediaFile from '@/Components/Media/MediaFile.vue'
import Masonry from '@/Components/Layout/Masonry.vue'
import SectionTitle from '@/Components/DataDisplay/SectionTitle.vue'
import NoResult from '@/Components/Util/NoResult.vue'

const workspaceCtx = inject('workspaceCtx')

const props = defineProps({
  columns: {
    type: Number,
    default: 3
  },
  columnWidth: {
    type: Number,
    default: 220
  },
  adaptive: {
    type: Boolean,
    default: true
  },
  maxSelectedItems: {
    type: Number,
    default: -1 //infinite
  },
  mimeTypes: {
    type: Array,
    default: () => []
  },
  selectedItems: {
    type: Array,
    default: () => []
  }
})

const {
  items,
  endlessPagination,
  selected,
  toggleSelect,
  deselectAll,
  updateItem,
  removeItems,
  isSelected,
  createObserver
} = useMedia(
  'mixpost.media.fetchUploads',
  { workspace: workspaceCtx.id },
  props.maxSelectedItems,
  props.mimeTypes,
  props.selectedItems
)

onMounted(() => {
  createObserver()
})

defineEmits(['close', 'insert'])
defineExpose({ selected, deselectAll, removeItems })
</script>
<template>
  <UploadMedia
    :max-selection="4"
    :selected="selected"
    :toggle-select="toggleSelect"
    :is-selected="isSelected"
    :columns="columns"
    :column-width="columnWidth"
    :adaptive="adaptive"
    :mime-types="props.mimeTypes"
  />

  <div :class="{ 'mt-lg': items.length }">
    <template v-if="items.length">
      <SectionTitle class="mb-4">{{ $t('media.library') }}</SectionTitle>

      <Masonry :items="items" :columns="columns" :column-width="columnWidth" :adaptive="adaptive">
        <template #default="{ item }">
          <MediaSelectable v-if="item" :active="isSelected(item)" @click="toggleSelect(item)">
            <MediaFile :media="item" @update="updateItem" @remove="removeItems([item.id])" />
          </MediaSelectable>
        </template>
      </Masonry>
    </template>
    <NoResult v-else class="mt-lg">{{ $t('media.no_images_found') }}</NoResult>
    <div ref="endlessPagination" class="-z-10 w-full" />
  </div>
</template>
