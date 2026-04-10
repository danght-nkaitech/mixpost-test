import { ref } from 'vue'
import usePostVersions from './usePostVersions'

const usePostMetaOptionsValidate = () => {
  const { getAccountVersion } = usePostVersions()

  const postTypeDisabled = ref(false)
  const postReelDisabled = ref(false)

  const validatePostType = ({ options, activeVersion, versions }) => {
    const accountVersion = getAccountVersion(versions, activeVersion)

    if (!accountVersion) {
      return
    }

    const hasVideo = accountVersion.content.some(contentItem =>
      contentItem.media.some(file => file.is_video === true)
    )

    const hasImage = accountVersion.content.some(contentItem =>
      contentItem.media.some(file => file.is_video === false)
    )

    if (hasImage) {
      postReelDisabled.value = true
      postTypeDisabled.value = false

      if (options.type === 'reel') {
        options.type = 'post'
      }

      return
    }

    if (hasVideo) {
      postReelDisabled.value = false
      postTypeDisabled.value = true

      if (options.type === 'post') {
        options.type = 'reel'
      }

      return
    }

    postReelDisabled.value = false
    postTypeDisabled.value = false
  }

  return {
    postTypeDisabled,
    postReelDisabled,
    validatePostType
  }
}

export default usePostMetaOptionsValidate
