import { inject, reactive, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import NProgress from 'nprogress'
import usePreloader from '@/Composables/usePreloader.js'
import useMedia from '@/Composables/useMedia.js'
import useRemoteUpload from '@/Composables/useRemoteUpload.js'
import { base64ToFile, applyLowZIndex, removeLowZIndex } from '@/helpers.js'
import useNotifications from './useNotifications.js'
import { useI18n } from 'vue-i18n'
import { nanoid } from 'nanoid'

const useAdobeExpress = emit => {
  const { notify } = useNotifications()
  const { t: $t } = useI18n()
  const { startPreloader, stopPreloader } = usePreloader({ useGlobal: true })

  const routePrefix = inject('routePrefix')
  const workspaceCtx = inject('workspaceCtx')

  const customSize = reactive({
    width: 1080,
    height: 1080,
    unit: 'px'
  })

  const socialMediaSizes = [
    {
      name: $t('media.fb_post'),
      value: 'Facebook'
    },
    {
      name: $t('media.fb_story'),
      value: 'FacebookStory'
    },
    {
      name: $t('media.ig_portrait_post'),
      value: 'InstagramPortraitPost'
    },
    {
      name: $t('media.ig_square_post'),
      value: 'Instagram'
    },
    {
      name: $t('media.ig_story'),
      value: 'InstagramStory'
    },
    {
      name: $t('media.linkedin_post'),
      value: 'LinkedinPost'
    },
    {
      name: $t('media.x_post'),
      value: 'Twitter'
    },
    {
      name: $t('media.pinterest_post'),
      value: 'Pinterest'
    }
  ]

  const { uploadFromUrl } = useRemoteUpload()

  const selected = ref([])
  let toggleSelect = () => {}

  if (emit) {
    const mediaComposable = useMedia(
      `${routePrefix}.media.fetchUploads`,
      { workspace: workspaceCtx.id },
      1
    )
    selected.value = mediaComposable.selected
    toggleSelect = mediaComposable.toggleSelect
  }

  const loadAdobeSdk = async () => {
    if (window.ccEverywhereInstance) {
      return
    }

    if (!window.CCEverywhere) {
      await new Promise((resolve, reject) => {
        const script = document.createElement('script')
        script.src = 'https://cc-embed.adobe.com/sdk/v4/CCEverywhere.js'
        script.async = true
        script.onload = resolve
        script.onerror = reject
        document.body.appendChild(script)
      })
    }

    window.ccEverywhereInstance = await window.CCEverywhere.initialize({
      clientId: usePage().props.service_configs.adobe_express.client_id,
      appName: usePage().props.app.name
    })
  }

  const uploadFile = async asset => {
    NProgress.start()
    startPreloader()

    try {
      const media =
        asset.dataType === 'base64' ? await uploadBase64(asset) : await uploadFromUrl(asset.data)

      NProgress.done()
      stopPreloader()

      return media
    } catch (error) {
      NProgress.done()
      stopPreloader()
      throw error
    }
  }

  const uploadBase64 = async asset => {
    const formData = new FormData()
    formData.append('adobe_express_doc_id', asset.documentId)
    formData.append('file', await base64ToFile(asset.data, nanoid()))

    const response = await axios.post(
      route(`${routePrefix}.media.upload`, { workspace: workspaceCtx.id }),
      formData
    )

    return response.data
  }

  /*
   * 'create' requires only 'canvasSize' param
   * 'edit' requires 'documentId' && 'media' params
   */
  const openAdobeExpressEditor = async ({ canvasSize = null, documentId = null, media = null }) => {
    await loadAdobeSdk()

    const isCreateMode = canvasSize !== null && !documentId && !media
    const isEditMode = !isCreateMode

    const docConfig = {}
    const appConfig = {
      allowedFileTypes: ['image/jpeg', 'video/mp4'],
      callbacks: {
        onLoadInit: applyUIChanges,
        onCancel: resetUIChanges,
        onPublish: async (intent, publishParams) => {
          resetUIChanges()

          const asset = publishParams.asset[0]
          asset.documentId = publishParams.documentId

          const uploadedMedia = await uploadFile(asset)

          notify('success', $t('media.saved'))

          if (isCreateMode && emit) {
            toggleSelect(uploadedMedia)
            emit('insert')
            emit('selectMediaInMediaLibrary', uploadedMedia)
            emit('close')
          }

          if (isEditMode) {
            Object.keys(uploadedMedia).forEach(key => {
              if (key in media) {
                media[key] = uploadedMedia[key]
              }
            })
          }
        }
      }
    }

    if (isCreateMode) {
      docConfig.canvasSize =
        typeof canvasSize === 'object'
          ? {
              width: parseInt(canvasSize.width),
              height: parseInt(canvasSize.height),
              unit: canvasSize.unit
            }
          : canvasSize

      window.ccEverywhereInstance.editor.create(docConfig, appConfig)
    }

    if (isEditMode) {
      docConfig.documentId = documentId
      appConfig.selectedCategory = 'media'
      window.ccEverywhereInstance.editor.edit(docConfig, appConfig)
    }
  }

  const applyUIChanges = () => {
    applyLowZIndex({ classes: ['default-sidebar', 'modal-visible'] })
  }

  const resetUIChanges = () => {
    removeLowZIndex({ classes: ['default-sidebar', 'modal-visible'] })
  }

  return {
    socialMediaSizes,
    customSize,
    openAdobeExpressEditor,
    selected
  }
}

export default useAdobeExpress
