import { ref } from 'vue'
import useConfig from '@/Composables/useConfig'
import useNotifications from '@/Composables/useNotifications'

const CHUNK_SIZE = 10 * 1024 * 1024 // 10MB default
const MAX_RETRIES = 3

const useChunkedUpload = ({ routePrefix, workspaceId }) => {
  const { chunkedUpload } = useConfig()
  const { notify } = useNotifications()

  const progress = ref(0)
  const status = ref('idle') // idle, initiating, uploading, completing, complete, error
  const error = ref(null)
  const isAborted = ref(false)
  const currentUploadUuid = ref(null)

  const threshold = chunkedUpload?.threshold || CHUNK_SIZE

  const shouldChunk = file => {
    return file.size > threshold
  }

  const reset = () => {
    progress.value = 0
    status.value = 'idle'
    error.value = null
    isAborted.value = false
    currentUploadUuid.value = null
  }

  const initiate = async (file, metadata = {}) => {
    status.value = 'initiating'
    error.value = null

    const response = await axios.post(
      route(`${routePrefix}.media.chunked.initiate`, { workspace: workspaceId }),
      {
        filename: file.name,
        mime_type: file.type,
        total_size: file.size,
        ...metadata
      }
    )

    currentUploadUuid.value = response.data.upload_uuid

    return response.data
  }

  const uploadChunk = async (uploadUuid, chunkIndex, chunkBlob, retries = 0) => {
    if (isAborted.value) {
      throw new Error('Upload aborted')
    }

    const formData = new FormData()
    formData.append('chunk', chunkBlob, `chunk_${chunkIndex}`)
    formData.append('chunk_index', chunkIndex)

    try {
      const response = await axios.post(
        route(`${routePrefix}.media.chunked.upload`, {
          workspace: workspaceId,
          uploadUuid
        }),
        formData
      )

      return response.data
    } catch (err) {
      if (retries < MAX_RETRIES && !isAborted.value) {
        await new Promise(resolve => setTimeout(resolve, 1000 * (retries + 1)))
        return uploadChunk(uploadUuid, chunkIndex, chunkBlob, retries + 1)
      }
      throw err
    }
  }

  const uploadAllChunks = async (uploadUuid, file, totalChunks, serverChunkSize) => {
    status.value = 'uploading'

    for (let i = 0; i < totalChunks; i++) {
      if (isAborted.value) {
        throw new Error('Upload aborted')
      }

      const start = i * serverChunkSize
      const end = Math.min(start + serverChunkSize, file.size)
      const chunkBlob = file.slice(start, end)

      await uploadChunk(uploadUuid, i, chunkBlob)

      progress.value = Math.round(((i + 1) / totalChunks) * 99) // Reserve 1% for completion
    }
  }

  const complete = async (uploadUuid, metadata = {}) => {
    status.value = 'completing'

    const response = await axios.post(
      route(`${routePrefix}.media.chunked.complete`, {
        workspace: workspaceId,
        uploadUuid
      }),
      metadata
    )

    progress.value = 100
    status.value = 'complete'

    return response.data
  }

  const abortUpload = async uploadUuid => {
    isAborted.value = true

    try {
      await axios.delete(
        route(`${routePrefix}.media.chunked.abort`, {
          workspace: workspaceId,
          uploadUuid
        })
      )
    } catch {
      // Ignore errors during abort
    }

    reset()
  }

  const upload = async (file, metadata = {}) => {
    reset()

    try {
      const initResult = await initiate(file, metadata)
      const {
        upload_uuid: uploadUuid,
        total_chunks: totalChunks,
        chunk_size: serverChunkSize
      } = initResult

      await uploadAllChunks(uploadUuid, file, totalChunks, serverChunkSize)

      return await complete(uploadUuid, metadata)
    } catch (err) {
      if (!isAborted.value) {
        status.value = 'error'
        error.value = err.response?.data?.message || err.message || 'Upload failed'
        notify('error', error.value)
      }
      throw err
    }
  }

  const regularUpload = async (file, metadata = {}) => {
    reset()
    status.value = 'uploading'

    try {
      const formData = new FormData()
      formData.append('file', file)

      Object.entries(metadata).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
          formData.append(key, value)
        }
      })

      const response = await axios.post(
        route(`${routePrefix}.media.upload`, { workspace: workspaceId }),
        formData,
        {
          onUploadProgress: progressEvent => {
            if (progressEvent.total) {
              progress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100)
            }
          }
        }
      )

      status.value = 'complete'
      return response.data
    } catch (err) {
      status.value = 'error'
      error.value = err.response?.data?.message || err.message || 'Upload failed'
      notify('error', error.value)
      throw err
    }
  }

  const smartUpload = async (file, metadata = {}) => {
    if (shouldChunk(file)) {
      return upload(file, metadata)
    }
    return regularUpload(file, metadata)
  }

  const abort = () => {
    if (currentUploadUuid.value) {
      abortUpload(currentUploadUuid.value)
    } else {
      reset()
    }
  }

  return {
    progress,
    status,
    error,
    shouldChunk,
    upload,
    regularUpload,
    smartUpload,
    abort,
    reset
  }
}

export default useChunkedUpload
