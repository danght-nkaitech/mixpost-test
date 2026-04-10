<script setup>
import { computed, ref, inject } from 'vue'
import { clone } from 'lodash'
import { useForm, usePage } from '@inertiajs/vue3'
import ExclamationCircleIcon from '@/Icons/ExclamationCircle.vue'
import VideoSolidIcon from '@/Icons/VideoSolid.vue'
import EllipsisVertical from '../../Icons/EllipsisVertical.vue'
import PureButton from '../Button/PureButton.vue'
import DropdownItem from '../Dropdown/DropdownItem.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import TrashIcon from '@/Icons/Trash.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import DialogModal from '@/Components/Modal/DialogModal.vue'
import Badge from '@/Components/DataDisplay/Badge.vue'
import Input from '@/Components/Form/Input.vue'
import DangerButton from '@/Components/Button/DangerButton.vue'
import Placeholder from '@/Components/Media/Placeholder.vue'
import MagnifyingGlass from '@/Icons/MagnifyingGlass.vue'
import PlayIcon from '@/Icons/Play.vue'
import MediaPreview from '@/Components/Media/MediaPreview.vue'
import AltTextDialog from '@/Components/Media/AltTextDialog.vue'
import VerticalGroup from '@/Components/Layout/VerticalGroup.vue'
import Logo from '@/Components/DataDisplay/Logo.vue'
import useAdobeExpress from '@/Composables/useAdobeExpress.js'

const props = defineProps({
  media: {
    type: Object,
    required: true
  },
  imgHeight: {
    type: String,
    default: 'full'
  },
  placeholderHeight: {
    type: String,
    default: 'default'
  },
  imgWidthFull: {
    type: Boolean,
    default: true
  },
  showCaption: {
    type: Boolean,
    default: true
  },
  showMenu: {
    type: Boolean,
    default: true
  },
  zoomable: {
    type: Boolean,
    default: true
  }
})

const workspaceCtx = inject('workspaceCtx')
const routePrefix = inject('routePrefix')

const emits = defineEmits(['update', 'remove'])

const imgHeightClass = computed(() => {
  return {
    full: 'h-full',
    sm: 'h-20'
  }[props.imgHeight]
})

const showPreview = ref(false)

const openPreview = () => {
  showPreview.value = true
}

const closePreview = () => {
  showPreview.value = false
}

// Rename
const showRenameForm = ref(false)

const renameForm = useForm({
  name: ''
})

const openRenameForm = () => {
  renameForm.name = props.media.name
  showRenameForm.value = true
}

const closeRenameForm = () => {
  showRenameForm.value = false
  renameForm.reset()
}

const updateName = () => {
  renameForm.put(
    route(`${routePrefix}.media.update`, {
      item: props.media.uuid,
      workspace: workspaceCtx.id
    }),
    {
      onSuccess: () => {
        const localMedia = clone(props.media)
        localMedia.name = renameForm.name
        emits('update', localMedia)
        closeRenameForm()
      }
    }
  )
}

// Delete
const showDeleteConfirm = ref(false)

const confirmDelete = () => {
  showDeleteConfirm.value = true
}

const deleteMedia = () => {
  axios
    .delete(route(`${routePrefix}.media.delete`, { workspace: workspaceCtx.id }), {
      data: { items: [props.media.id] }
    })
    .then(() => {
      showDeleteConfirm.value = false
      emits('remove')
    })
}

// Alt text
const altTextMedia = ref(null)

const openAltTextForm = () => {
  altTextMedia.value = props.media
}

const onAltTextUpdated = updatedMedia => {
  emits('update', updatedMedia)
  altTextMedia.value = null
}

const { openAdobeExpressEditor } = useAdobeExpress()
</script>
<template>
  <figure
    :class="{ 'border border-gray-200 rounded-md p-xs bg-stone-500': showCaption }"
    class="group relative"
  >
    <slot />
    <div
      class="relative flex rounded"
      :class="{ 'border border-red-500 p-md': media.hasOwnProperty('error') }"
    >
      <span v-if="media.is_video && media.thumb_url" class="absolute top-0 left-0 mt-1 ml-1">
        <VideoSolidIcon class="!w-4 !h-4 text-white" />
      </span>

      <div v-if="media.hasOwnProperty('error')" class="text-center">
        <ExclamationCircleIcon class="w-8 h-8 mx-auto text-red-500" />
        <div class="mt-xs">{{ media.name }}</div>
        <div class="mt-xs text-red-500">
          {{ media.error ? media.error : $t('media.error_uploading_media') }}
        </div>
      </div>

      <template v-if="media.alt_text">
        <Badge
          v-tooltip="{ content: media.alt_text, triggers: ['click'], autoHide: true }"
          variant="dark"
          class="absolute bottom-[3px] left-[3px] text-xs font-medium"
        >
          <span class="uppercase">alt</span>
        </Badge>
      </template>

      <template v-if="media.thumb_url">
        <img
          :src="media.thumb_url"
          :alt="`Image: ${media.name}`"
          loading="lazy"
          class="rounded-md"
          :class="[imgHeightClass, { 'w-full': imgWidthFull }]"
        />
      </template>

      <template v-if="media.is_video && !media.thumb_url">
        <Placeholder
          type="video"
          :width-class="imgWidthFull ? 'w-full' : 'w-28'"
          :height-class="{ default: 'h-28', sm: 'h-20' }[placeholderHeight]"
        />
      </template>

      <button
        v-if="zoomable && (media.thumb_url || media.url)"
        class="absolute bottom-1 right-1 w-7 h-7 bg-black/60 hover:bg-black/80 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
        @click.stop="openPreview"
      >
        <PlayIcon v-if="media.is_video" class="!w-4 !h-4 text-white" />
        <MagnifyingGlass v-else class="!w-4 !h-4 text-white" />
      </button>
    </div>

    <template v-if="showCaption">
      <figcaption class="mt-xs flex items-start justify-between gap-xs">
        <div class="flex flex-col gap-1">
          <div>
            <span class="text-sm break-all flex-1 min-w-0">{{ media.name }}</span>
          </div>
          <slot name="credit" />
        </div>
        <!--Main Menu-->
        <Dropdown v-if="showMenu" width-classes="w-auto" placement="bottom-end">
          <template #trigger>
            <PureButton
              class="shrink-0 w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors"
              @click.stop
            >
              <template #icon>
                <EllipsisVertical class="!w-4 !h-4 text-gray-500" />
              </template>
            </PureButton>
          </template>
          <template #content>
            <template
              v-if="
                media.adobe_express_doc_id && usePage().props.is_configured_service.adobe_express
              "
            >
              <DropdownItem
                as="button"
                @click="
                  () =>
                    openAdobeExpressEditor({
                      documentId: media.adobe_express_doc_id,
                      media: media
                    })
                "
              >
                <template #icon>
                  <Logo classes="h-6" />
                </template>
                {{ $t('media.create_for_adobe_express') }}
              </DropdownItem>
            </template>
            <DropdownItem as="button" @click="openRenameForm">
              <template #icon>
                <PencilSquare />
              </template>
              {{ $t('general.rename') }}
            </DropdownItem>
            <template v-if="!media.is_video">
              <DropdownItem as="button" @click="openAltTextForm">
                <template #icon>
                  <PencilSquare />
                </template>
                {{ $t('media.edit_alt_text') }}
              </DropdownItem>
            </template>
            <DropdownItem as="button" @click="confirmDelete">
              <template #icon>
                <TrashIcon class="text-red-500" />
              </template>
              {{ $t('general.delete') }}
            </DropdownItem>
          </template>
        </Dropdown>
      </figcaption>
    </template>
  </figure>

  <MediaPreview v-if="zoomable" :show="showPreview" :media="media" @close="closePreview" />

  <AltTextDialog
    :show="!!altTextMedia"
    :media="altTextMedia"
    @updated="onAltTextUpdated"
    @close="altTextMedia = null"
  />

  <DialogModal :show="showRenameForm" max-width="md" :closeable="true" @close="closeRenameForm">
    <template #header>
      {{ $t('general.rename') }}
    </template>
    <template #body>
      <VerticalGroup :force-full-width="true">
        <template #title>{{ $t('general.name') }}</template>
        <Input v-model="renameForm.name" type="text" @keydown.enter="updateName" />
      </VerticalGroup>
    </template>
    <template #footer>
      <SecondaryButton class="mr-xs rtl:mr-0 rtl:ml-xs" @click="closeRenameForm">
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <PrimaryButton
        :is-loading="renameForm.processing"
        :disabled="renameForm.processing || !renameForm.name.trim()"
        @click="updateName"
      >
        {{ $t('general.save') }}
      </PrimaryButton>
    </template>
  </DialogModal>

  <DialogModal
    :show="showDeleteConfirm"
    max-width="md"
    :closeable="true"
    @close="showDeleteConfirm = false"
  >
    <template #header>
      {{ $t('general.delete') }}
    </template>
    <template #body>
      {{ $t('media.do_you_want_delete') }}
    </template>
    <template #footer>
      <SecondaryButton class="mr-xs rtl:mr-0 rtl:ml-xs" @click="showDeleteConfirm = false">
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <DangerButton @click="deleteMedia">
        {{ $t('general.delete') }}
      </DangerButton>
    </template>
  </DialogModal>
</template>
