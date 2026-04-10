import { computed } from 'vue'

const useProviderClassesColor = provider => {
  const textClasses = computed(() => {
    return {
      facebook: 'text-facebook',
      facebook_page: 'text-facebook',
      facebook_group: 'text-facebook',
      instagram: 'text-instagram',
      threads: 'text-threads',
      mastodon: 'text-mastodon',
      youtube: 'text-youtube',
      gbp: 'text-gbp',
      pinterest: 'text-pinterest',
      twitter: 'text-twitter',
      linkedin: 'text-linkedin',
      linkedin_page: 'text-linkedin',
      tiktok: 'text-tiktok',
      bluesky: 'text-bluesky'
    }[provider]
  })

  const borderClasses = computed(() => {
    return {
      facebook: 'border-facebook',
      facebook_page: 'border-facebook',
      facebook_group: 'border-facebook',
      instagram: 'border-instagram',
      threads: 'border-threads',
      mastodon: 'border-mastodon',
      youtube: 'border-youtube',
      gbp: 'border-gbp',
      pinterest: 'border-pinterest',
      twitter: 'border-twitter',
      linkedin: 'border-linkedin',
      linkedin_page: 'border-linkedin',
      tiktok: 'border-tiktok',
      bluesky: 'border-bluesky'
    }[provider]
  })

  const activeBgClasses = computed(() => {
    return {
      facebook: 'bg-facebook',
      facebook_page: 'bg-facebook',
      facebook_group: 'bg-facebook',
      instagram: 'bg-instagram',
      threads: 'bg-threads',
      mastodon: 'bg-mastodon',
      youtube: 'bg-youtube',
      gbp: 'bg-gbp',
      pinterest: 'bg-pinterest',
      twitter: 'bg-twitter',
      linkedin: 'bg-linkedin',
      linkedin_page: 'bg-linkedin',
      tiktok: 'bg-tiktok',
      bluesky: 'bg-bluesky'
    }[provider]
  })

  return {
    textClasses,
    borderClasses,
    activeBgClasses
  }
}

export default useProviderClassesColor
