<script setup>
import { computed, inject, onMounted, onUnmounted, ref, watch } from 'vue'
import { nanoid } from 'nanoid'
import emitter from '@/Services/emitter'
import useConfig from '@/Composables/useConfig'
import useChunkedUpload from '@/Composables/useChunkedUpload'
import useRemoteUpload from '@/Composables/useRemoteUpload'
import Masonry from '@/Components/Layout/Masonry.vue'
import MediaFile from '@/Components/Media/MediaFile.vue'
import MediaSelectable from '@/Components/Media/MediaSelectable.vue'
import UploadProgressPanel from '@/Components/Media/UploadProgressPanel.vue'
import PhotoIcon from '@/Icons/Photo.vue'
import LinkIcon from '@/Icons/Link.vue'
import Input from '@/Components/Form/Input.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'

const routePrefix = inject('routePrefix')
const workspaceCtx = inject('workspaceCtx')
const { mimeTypes: configMimeTypes } = useConfig()
const { uploadFromUrl: remoteUploadFromUrl } = useRemoteUpload()

const props = defineProps({
  maxSelection: {
    type: Number,
    default: 1
  },
  selected: {
    type: Array,
    default: () => []
  },
  toggleSelect: {
    type: Function
  },
  isSelected: {
    type: Function
  },
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
  mimeTypes: {
    type: Array,
    default: () => []
  }
})

defineEmits(['mediaSelect'])

const mimeTypes = props.mimeTypes.length ? props.mimeTypes : configMimeTypes

const input = ref(null)

const dragEnter = ref(false)

const onDrop = e => {
  if (isLoading.value) {
    return
  }

  dragEnter.value = false

  const files = filterFiles(e.dataTransfer.files)

  if (files.length) {
    dispatch(files)
  }
}

const onBrowse = e => {
  const files = filterFiles(e.target.files)

  if (files.length) {
    input.value.value = null
    dispatch(files)
  }
}

const filterFiles = files => {
  return Array.from(files).filter(file => {
    return mimeTypes.includes(file.type)
  })
}

const isLoading = ref(false)
const pending = ref([])
const completed = ref([])
const active = ref({})
const uploadJobs = ref([])

watch(active, () => {
  processJob()

  isLoading.value = Object.keys(active.value).length > 0
})

const processJob = () => {
  if (active.value.handler) {
    active.value.handler()
  }
}

const addJob = job => {
  pending.value.push(job)

  if (Object.keys(active.value).length === 0) {
    startNextJob()
  }
}

const startNextJob = media => {
  if (Object.keys(active.value).length > 0) {
    addCompletedJob(active.value, media)

    if (props.toggleSelect) {
      props.toggleSelect(media)
    }
  }

  if (pending.value.length > 0) {
    setActiveJob(pending.value[0])
    popCurrentJob()
  } else {
    setActiveJob({})
  }
}

const setActiveJob = job => {
  active.value = job
}

const popCurrentJob = () => {
  pending.value.shift()
}

const addCompletedJob = (job, media) => {
  completed.value.push(Object.assign(job, { media }))
}

const getJob = jobId => {
  return uploadJobs.value.find(job => job.id === jobId)
}

const dispatch = files => {
  files.forEach(file => {
    const jobId = nanoid()

    uploadJobs.value.push({
      id: jobId,
      file,
      progress: 0,
      status: 'pending',
      error: null,
      uploadInstance: null
    })

    addJob({
      id: jobId,
      handler: async () => {
        const job = getJob(jobId)
        await uploadFileWithProgress(job)
          .then(media => {
            job.status = 'complete'
            job.progress = 100
            startNextJob(media)
          })
          .catch(error => {
            job.status = 'error'
            job.error = error.response?.data?.message || error.message || 'Upload failed'
            startNextJob({
              name: file.name,
              error: job.error
            })
          })
      }
    })
  })
}

const uploadFileWithProgress = async job => {
  job.status = 'uploading'

  // Create a fresh upload instance for this specific file
  const uploader = useChunkedUpload({ routePrefix, workspaceId: workspaceCtx.id })
  job.uploadInstance = uploader

  const shouldUseChunked = uploader.shouldChunk(job.file)

  if (shouldUseChunked) {
    const progressInterval = setInterval(() => {
      job.progress = uploader.progress.value
    }, 100)

    try {
      const media = await uploader.smartUpload(job.file)
      clearInterval(progressInterval)
      return media
    } catch (error) {
      clearInterval(progressInterval)
      throw error
    }
  }

  return new Promise((resolve, reject) => {
    const formData = new FormData()
    formData.append('file', job.file)

    axios
      .post(route(`${routePrefix}.media.upload`, { workspace: workspaceCtx.id }), formData, {
        onUploadProgress: progressEvent => {
          if (progressEvent.total) {
            job.progress = Math.round((progressEvent.loaded / progressEvent.total) * 100)
          }
        }
      })
      .then(response => {
        resolve(response.data)
      })
      .catch(error => {
        reject(error)
      })
  })
}

const cancelUpload = job => {
  if (job.status === 'uploading' && job.uploadInstance) {
    job.uploadInstance.abort()
  }
  job.status = 'error'
  job.error = 'Cancelled'
  uploadJobs.value = uploadJobs.value.filter(uploadJob => uploadJob.id !== job.id)
}

const cancelAllUploads = () => {
  activeUploadJobs.value.forEach(job => {
    if (job.uploadInstance) {
      job.uploadInstance.abort()
    }
  })
  pending.value = []
  active.value = {}
  uploadJobs.value = []
}

const activeUploadJobs = computed(() => {
  return uploadJobs.value.filter(job => job.status !== 'complete')
})

const completedJobs = computed(() => {
  return completed.value.filter(() => true).reverse()
})

const updateCompletedItem = updatedMedia => {
  const job = completed.value.find(job => job.media?.id === updatedMedia.id)
  if (job) {
    job.media = { ...job.media, ...updatedMedia }
  }
}

const removeCompletedItem = id => {
  completed.value = completed.value.filter(job => job.media?.id !== id)
}

onMounted(() => {
  emitter.on('mediaDelete', ids => {
    completed.value = completed.value.filter(job => {
      return !ids.includes(job.media.id)
    })
  })
})

onUnmounted(() => {
  emitter.off('mediaDelete')
})

// URL Upload functionality
const urlInput = ref('')
const urlError = ref('')
const isUrlUploading = ref(false)

const extractFilenameFromUrl = url => {
  try {
    const urlObj = new URL(url)
    const pathname = urlObj.pathname
    const filename = pathname.split('/').pop()
    return filename || url
  } catch {
    return url
  }
}

const submitUrlUpload = async () => {
  const url = urlInput.value.trim()

  if (!url) {
    return
  }

  urlError.value = ''
  isUrlUploading.value = true

  const jobId = nanoid()
  const filename = extractFilenameFromUrl(url)

  uploadJobs.value.push({
    id: jobId,
    file: { name: filename },
    progress: 0,
    status: 'pending',
    error: null,
    uploadInstance: null,
    isRemote: true
  })

  addJob({
    id: jobId,
    handler: async () => {
      const job = getJob(jobId)
      await uploadFromUrl(job, url)
        .then(media => {
          job.status = 'complete'
          job.progress = 100
          startNextJob(media)
        })
        .catch(error => {
          job.status = 'error'
          job.error = error.response?.data?.message || error.message || 'Download failed'
          startNextJob({
            name: filename,
            error: job.error
          })
        })
    }
  })

  urlInput.value = ''
  isUrlUploading.value = false
}

const uploadFromUrl = async (job, url) => {
  job.status = 'uploading'

  return remoteUploadFromUrl(url, {
    onProgress: progress => {
      job.progress = progress
    }
  })
}

// DEV ONLY: Expose mock upload for console testing
if (import.meta.env.DEV) {
  import('@/Composables/dev/useMockUpload').then(({ createMockUpload }) => {
    window.mockUpload = () => createMockUpload(uploadJobs)
  })
}
</script>
<template>
  <div
    :class="{ 'border-gray-700 bg-white': !dragEnter, 'border-cyan-500 bg-cyan-50': dragEnter }"
    class="relative w-full flex items-center justify-center rounded-lg p-10 border-2 border-dashed transition-colors ease-in-out duration-200"
    @dragenter.prevent="dragEnter = !isLoading"
    @drop.prevent="onDrop"
    @dragover.prevent
  >
    <div class="relative flex flex-col justify-center">
      <div
        v-if="dragEnter"
        class="w-full h-full absolute"
        @dragleave.prevent="dragEnter = false"
        @dragover.prevent
      ></div>
      <PhotoIcon
        :class="{ 'text-stone-700': !dragEnter, 'text-cyan-500': dragEnter }"
        class="w-16! h-16! mx-auto mb-xs transition-colors ease-in-out duration-200"
      />
      <div class="text-center mb-1">
        {{ $t('media.drag_drop_files') }}
        <label
          for="browse"
          class="cursor-pointer text-primary-500 hover:text-primary-700 active:text-primary-700 focus:outline-hidden focus:text-primary-700 transition-colors ease-in-out duration-200"
        >
          {{ $t('general.browse') }}
        </label>
      </div>
      <div class="text-sm text-gray-400 text-center">{{ $t('media.supported_file_types') }}</div>
    </div>
  </div>

  <div class="mt-md">
    <div class="flex items-center gap-xs text-sm text-gray-500 mb-sm">
      <LinkIcon class="!w-4 !h-4" />
      <span>{{ $t('media.upload_from_url') }}</span>
    </div>
    <form class="flex gap-sm" @submit.prevent="submitUrlUpload">
      <Input
        v-model="urlInput"
        type="url"
        :placeholder="$t('media.paste_image_video_url')"
        :disabled="isLoading"
        :error="!!urlError"
        class="flex-1"
      />
      <PrimaryButton
        type="submit"
        :disabled="!urlInput.trim() || isLoading"
        :is-loading="isUrlUploading"
      >
        {{ $t('general.upload') }}
      </PrimaryButton>
    </form>
    <p v-if="urlError" class="mt-xs text-sm text-red-600">{{ urlError }}</p>
  </div>

  <UploadProgressPanel
    :jobs="activeUploadJobs"
    @cancel="cancelUpload"
    @cancel-all="cancelAllUploads"
  />

  <input
    id="browse"
    ref="input"
    type="file"
    :accept="mimeTypes.join(',')"
    multiple="multiple"
    class="hidden"
    @change="onBrowse"
  />

  <div v-if="completedJobs.length" class="mt-lg">
    <Masonry
      :items="completedJobs"
      :columns="columns"
      :column-width="columnWidth"
      :adaptive="adaptive"
    >
      <template #default="{ item }">
        <MediaSelectable :active="isSelected(item.media)" @click="toggleSelect(item.media)">
          <MediaFile
            :media="item.media"
            @update="updateCompletedItem"
            @remove="removeCompletedItem(item.media.id)"
          />
        </MediaSelectable>
      </template>
    </Masonry>
  </div>
</template>
