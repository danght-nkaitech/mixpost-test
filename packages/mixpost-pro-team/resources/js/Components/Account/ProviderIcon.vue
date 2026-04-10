<script setup>
import { computed } from 'vue'
import useProviderClassesColor from '@/Composables/useProviderClassesColor'
import TwitterIcon from '@/Icons/Twitter.vue'
import FacebookIcon from '@/Icons/Facebook.vue'
import InstagramIcon from '@/Icons/Instagram.vue'
import MastodonIcon from '@/Icons/Mastodon.vue'
import YoutubeIcon from '@/Icons/Youtube.vue'
import GBPIcon from '@/Icons/GBP.vue'
import PinterestIcon from '@/Icons/Pinterest.vue'
import LinkedinIcon from '@/Icons/Linkedin.vue'
import TikTokIcon from '@/Icons/TikTok.vue'
import Threads from '../../Icons/Threads.vue'
import Bluesky from '../../Icons/Bluesky.vue'

const props = defineProps({
  provider: {
    type: String,
    required: true
  }
})

const providers = {
  twitter: TwitterIcon,
  facebook: FacebookIcon,
  facebook_page: FacebookIcon,
  facebook_group: FacebookIcon,
  instagram: InstagramIcon,
  threads: Threads,
  mastodon: MastodonIcon,
  youtube: YoutubeIcon,
  gbp: GBPIcon,
  pinterest: PinterestIcon,
  linkedin: LinkedinIcon,
  linkedin_page: LinkedinIcon,
  tiktok: TikTokIcon,
  bluesky: Bluesky
}

const component = computed(() => {
  return providers[props.provider]
})

const { textClasses } = useProviderClassesColor(props.provider)
</script>
<template>
  <component :is="component" :class="textClasses" />
</template>
