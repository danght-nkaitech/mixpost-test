<script setup>
import { computed, inject, onMounted } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import useAuth from '../../Composables/useAuth'
import useMedia from '@/Composables/useMedia'
import MediaSelectable from '@/Components/Media/MediaSelectable.vue'
import MediaFile from '@/Components/Media/MediaFile.vue'
import Masonry from '@/Components/Layout/Masonry.vue'
import SearchInput from '@/Components/Util/SearchInput.vue'
import MediaCredit from '@/Components/Media/MediaCredit.vue'
import NoResult from '@/Components/Util/NoResult.vue'
import Alert from '@/Components/Util/Alert.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import { capitalize } from 'lodash'

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
  }
})

const { user } = useAuth()

const stockPhotoProvider = computed(() => {
  return usePage().props.stock_photo_provider
})

const enabled = computed(() => {
  return usePage().props.is_configured_service[stockPhotoProvider.value]
})

const {
  isLoaded,
  keyword,
  items,
  endlessPagination,
  selected,
  toggleSelect,
  deselectAll,
  updateItem,
  removeItems,
  isSelected,
  createObserver
} = useMedia('mixpost.media.fetchStock', { workspace: workspaceCtx.id }, props.maxSelectedItems)

onMounted(() => {
  if (enabled.value) {
    createObserver()
  }
})

defineEmits(['close', 'insert'])
defineExpose({ selected, deselectAll })
</script>
<template>
  <div v-if="enabled">
    <SearchInput
      v-model="keyword"
      :placeholder="$t(`service.stock_photo.search`, { provider: capitalize(stockPhotoProvider) })"
    />

    <div v-if="items.length" class="mt-lg">
      <Masonry :items="items" :columns="columns" :column-width="columnWidth" :adaptive="adaptive">
        <template #default="{ item }">
          <MediaSelectable v-if="item" :active="isSelected(item)" @click="toggleSelect(item)">
            <MediaFile
              :key="item.id"
              :media="item"
              :show-menu="false"
              @update="updateItem"
              @remove="removeItems([item.id])"
            >
              <template #credit>
                <MediaCredit
                  :source-url="item.source_url"
                  :provider="stockPhotoProvider"
                  :credit-url="item.credit_url"
                  :author-name="item.name"
                />
              </template>
            </MediaFile>
          </MediaSelectable>
        </template>
      </Masonry>
    </div>

    <NoResult v-if="isLoaded && !items.length" class="mt-lg">{{
      $t('media.no_images_found')
    }}</NoResult>

    <div ref="endlessPagination" class="-z-10 w-full" />
  </div>

  <template v-if="!enabled">
    <Alert variant="warning" :closeable="false">
      {{ $t('service.not_configured_service', { service: `${capitalize(stockPhotoProvider)}` }) }}
    </Alert>

    <template v-if="user.is_admin">
      <Link :href="route('mixpost.services.index')" class="block mt-md">
        <PrimaryButton>{{ $t('media.click_configure') }}</PrimaryButton>
      </Link>
    </template>
  </template>
</template>
