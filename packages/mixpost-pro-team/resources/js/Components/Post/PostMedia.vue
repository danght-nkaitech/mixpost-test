<script setup>
import { ref } from 'vue'
import { clone } from 'lodash'
import { usePage } from '@inertiajs/vue3'
import Draggable from 'vuedraggable'
import usePost from '@/Composables/usePost'
import useAdobeExpress from '@/Composables/useAdobeExpress.js'
import MediaFile from '@/Components/Media/MediaFile.vue'
import AltTextDialog from '@/Components/Media/AltTextDialog.vue'
import AddMedia from '@/Components/Media/AddMedia.vue'
import Dropdown from '@/Components/Dropdown/Dropdown.vue'
import DropdownItem from '@/Components/Dropdown/DropdownItem.vue'
import PureButton from '@/Components/Button/PureButton.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import DangerButton from '@/Components/Button/DangerButton.vue'
import DialogModal from '@/Components/Modal/DialogModal.vue'
import Flex from '@/Components/Layout/Flex.vue'
import AlertText from '@/Components/Util/AlertText.vue'
import EllipsisVertical from '@/Icons/EllipsisVertical.vue'
import PencilSquare from '@/Icons/PencilSquare.vue'
import TrashIcon from '@/Icons/Trash.vue'
import Photo from '@/Icons/Photo.vue'
import PhotoSolid from '@/Icons/PhotoSolid.vue'
import Logo from '@/Components/DataDisplay/Logo.vue'

const props = defineProps({
  media: {
    type: Array,
    required: true
  },
  showItemDropdownMenu: {
    type: Boolean,
    default: true
  },
  videoThumbs: {
    type: Array,
    required: true
  },
  enableVideoThumb: {
    type: Boolean,
    default: false
  },
  providersWithVideoThumbEnabled: {
    type: Array,
    default: () => []
  }
})

const emits = defineEmits(['updated', 'videoThumbsUpdated'])

const { editAllowed } = usePost()
const { openAdobeExpressEditor } = useAdobeExpress()

const remove = id => {
  const index = props.media.findIndex(item => item.id === id)

  const items = clone(props.media)
  items.splice(index, 1)

  //remove thumbnail only if there are no other media with the provided id
  if (props.media.findIndex(item => item.id === id)) {
    const thumbs = clone(props.videoThumbs).filter(thumb => {
      return thumb.media_id !== id
    })
    emits('videoThumbsUpdated', thumbs)
  }

  emits('updated', items)
}

const updateMediaAltText = media => {
  const items = clone(props.media)
  items.forEach(item => {
    if (item.id === media.id) {
      item.alt_text = media.alt_text
    }
  })
  emits('updated', items)
}

const syncVideoThumb = (media, mode, thumbnail = {}) => {
  if (!['add_thumbnail', 'remove_thumbnail'].includes(mode)) {
    return
  }

  const items = clone(props.media)
  let thumbs = clone(props.videoThumbs)

  items.forEach(item => {
    if (item.id === media.id) {
      thumbs = thumbs.filter(thumb => {
        return thumb.media_id !== media.id
      })
      if (mode === 'add_thumbnail') {
        item.video_custom_thumb_url = thumbnail.url
        thumbs.push({
          media_id: media.id,
          thumb_id: thumbnail.id
        })
      } else if (mode === 'remove_thumbnail') {
        item.video_custom_thumb_url = null
      }
    }
  })

  emits('updated', items)
  emits('videoThumbsUpdated', thumbs)
}

// Dropdown visibility
const shouldShowDropdown = element => {
  if (!editAllowed.value && !element.is_video) {
    return false
  }

  if (!editAllowed.value && element.is_video) {
    return element.video_custom_thumb_url !== null
  }

  return props.showItemDropdownMenu
}

// Alt text
const altTextMedia = ref(null)

const onAltTextUpdated = updatedMedia => {
  updateMediaAltText(updatedMedia)
  altTextMedia.value = null
}

// Video thumb viewer
const videoThumbViewMedia = ref(null)

const removeCustomVideoThumb = () => {
  if (!editAllowed.value || !videoThumbViewMedia.value) {
    return
  }

  syncVideoThumb(videoThumbViewMedia.value, 'remove_thumbnail')
  videoThumbViewMedia.value = null
}

// Video thumb selector
const videoThumbSelectMedia = ref(null)

const onVideoThumbSelected = thumbnail => {
  if (videoThumbSelectMedia.value) {
    syncVideoThumb(videoThumbSelectMedia.value, 'add_thumbnail', thumbnail)
  }
  videoThumbSelectMedia.value = null
}
</script>
<template>
  <div :class="{ 'mt-xs': media.length }">
    <Draggable
      :list="media"
      :disabled="!editAllowed"
      v-bind="{
        animation: 200,
        group: 'media'
      }"
      item-key="id"
      class="flex flex-wrap gap-sm"
    >
      <template #item="{ element }">
        <div role="button" class="cursor-pointer relative group">
          <MediaFile
            :media="element"
            img-height="sm"
            placeholder-height="sm"
            :img-width-full="false"
            :show-caption="false"
          />

          <span
            v-if="enableVideoThumb && element.is_video && element.video_custom_thumb_url"
            class="absolute top-0 left-0 mt-1 ml-1 pointer-events-none"
            :class="{ 'mt-6': element.thumb_url }"
          >
            <PhotoSolid
              class="!w-4 !h-4"
              :class="element.thumb_url ? 'text-white' : 'text-black'"
            />
          </span>

          <div v-if="shouldShowDropdown(element)" class="absolute top-0 -mt-xs right-0 -mr-xs">
            <Dropdown width-classes="w-auto" placement="top-start">
              <template #trigger>
                <PureButton
                  class="w-6 h-6 bg-white rounded-full flex items-center justify-center"
                  @click.prevent
                >
                  <template #icon>
                    <div class="bg-white border-2 border-gray-400 rounded-full">
                      <EllipsisVertical class="!w-5 !h-5" />
                    </div>
                  </template>
                </PureButton>
              </template>
              <template #content>
                <template
                  v-if="
                    element.adobe_express_doc_id &&
                    usePage().props.is_configured_service.adobe_express &&
                    editAllowed
                  "
                >
                  <DropdownItem
                    as="button"
                    @click="
                      () =>
                        openAdobeExpressEditor({
                          documentId: element.adobe_express_doc_id,
                          media: element
                        })
                    "
                  >
                    <template #icon>
                      <Logo classes="h-6" />
                    </template>
                    {{ $t('media.create_for_adobe_express') }}
                  </DropdownItem>
                </template>
                <template v-if="!element.is_video && editAllowed">
                  <DropdownItem as="button" @click="altTextMedia = element">
                    <template #icon>
                      <PencilSquare />
                    </template>
                    {{ $t('media.edit_alt_text') }}
                  </DropdownItem>
                </template>
                <template v-if="enableVideoThumb && element.is_video && editAllowed">
                  <DropdownItem as="button" @click="videoThumbSelectMedia = element">
                    <template #icon>
                      <Photo />
                    </template>
                    {{
                      element.video_custom_thumb_url
                        ? $t('media.change_video_thumb')
                        : $t('media.add_video_thumb')
                    }}
                  </DropdownItem>
                </template>
                <template
                  v-if="enableVideoThumb && element.is_video && element.video_custom_thumb_url"
                >
                  <DropdownItem as="button" @click="videoThumbViewMedia = element">
                    <template #icon>
                      <Photo />
                    </template>
                    {{ $t('media.view_video_thumb') }}
                  </DropdownItem>
                </template>
                <template v-if="editAllowed">
                  <DropdownItem as="button" @click="remove(element.id)">
                    <template #icon>
                      <TrashIcon class="text-red-500" />
                    </template>
                    {{ $t('general.remove') }}
                  </DropdownItem>
                </template>
              </template>
            </Dropdown>
          </div>
        </div>
      </template>
    </Draggable>
  </div>

  <AltTextDialog
    :show="!!altTextMedia"
    :media="altTextMedia"
    @updated="onAltTextUpdated"
    @close="altTextMedia = null"
  />

  <DialogModal :show="!!videoThumbViewMedia" @close="videoThumbViewMedia = null">
    <template #header>
      {{ $t('media.view_video_thumb') }}
    </template>
    <template #body>
      <AlertText variant="warning" class="mb-xs">
        {{
          $t('media.video_thumb_providers', {
            providers: providersWithVideoThumbEnabled.join(', ')
          })
        }}
      </AlertText>
      <figure v-if="videoThumbViewMedia">
        <img :src="videoThumbViewMedia.video_custom_thumb_url" :alt="$t('media.video_thumb')" />
      </figure>
    </template>
    <template #footer>
      <Flex>
        <template v-if="editAllowed">
          <DangerButton size="md" @click="removeCustomVideoThumb">
            <template #icon>
              <TrashIcon />
            </template>
            {{ $t('general.remove') }}
          </DangerButton>
        </template>
        <SecondaryButton class="mr-xs" @click="videoThumbViewMedia = null">
          {{ $t('general.close') }}
        </SecondaryButton>
      </Flex>
    </template>
  </DialogModal>

  <AddMedia
    v-if="enableVideoThumb"
    :disable-trigger="true"
    :max-selected-items="1"
    :mime-types="['image/jpg', 'image/jpeg', 'image/png']"
    :show-immediate="!!videoThumbSelectMedia"
    @insert="el => onVideoThumbSelected(el.items[0])"
    @close="videoThumbSelectMedia = null"
  />
</template>
