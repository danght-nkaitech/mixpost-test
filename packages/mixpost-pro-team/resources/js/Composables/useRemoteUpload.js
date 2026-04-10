import { inject } from 'vue'

const useRemoteUpload = () => {
  const routePrefix = inject('routePrefix')
  const workspaceCtx = inject('workspaceCtx')

  const uploadFromUrl = async (url, { onProgress } = {}) => {
    const response = await axios.post(
      route(`${routePrefix}.media.remote.initiate`, { workspace: workspaceCtx.id }),
      { url }
    )

    const { status, media, error, download_id: downloadId } = response.data

    if (status === 'completed' && media) {
      onProgress?.(100)
      return media
    }

    if (status === 'failed') {
      throw new Error(error || 'Download failed')
    }

    return new Promise((resolve, reject) => {
      const pollStatus = async () => {
        try {
          const statusResponse = await axios.get(
            route(`${routePrefix}.media.remote.status`, {
              workspace: workspaceCtx.id,
              downloadId
            })
          )

          const { status, progress, media, error } = statusResponse.data

          onProgress?.(progress || 0)

          if (status === 'completed' && media) {
            resolve(media)
            return
          }

          if (status === 'failed') {
            reject(new Error(error || 'Download failed'))
            return
          }

          setTimeout(pollStatus, 1000)
        } catch (error) {
          reject(error.response?.data?.error ? new Error(error.response.data.error) : error)
        }
      }

      pollStatus()
    })
  }

  return { uploadFromUrl }
}

export default useRemoteUpload
