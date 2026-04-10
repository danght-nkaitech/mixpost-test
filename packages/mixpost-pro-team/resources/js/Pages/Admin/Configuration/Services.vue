<script setup>
import { ref, defineAsyncComponent } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/Admin.vue'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Tabs from '@/Components/Navigation/Tabs.vue'
import Tab from '@/Components/Navigation/Tab.vue'
import GoogleIcon from '@/Icons/Google.vue'
import UnsplashIcon from '@/Icons/Unsplash.vue'
import ProviderIcon from '../../../Components/Account/ProviderIcon.vue'
import OpenAI from '../../../Icons/OpenAI.vue'
import Tenor from '../../../Icons/Tenor.vue'
import Bitly from '@/Icons/Bitly.vue'
import Yourls from '@/Icons/Yourls.vue'
import Shlink from '@/Icons/Shlink.vue'
import PexelsIcon from '@/Icons/Pexels.vue'
import Giphy from '@/Icons/Giphy.vue'

const FacebookServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/FacebookServiceForm.vue')
)
const ThreadsServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/ThreadsServiceForm.vue')
)
const BlueskyServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/BlueskyServiceForm.vue')
)
const GoogleServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/GoogleServiceForm.vue')
)
const PinterestServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/PinterestServiceForm.vue')
)
const LinkedInServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/LinkedInServiceForm.vue')
)
const TikTokServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/TikTokServiceForm.vue')
)
const TwitterServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/TwitterServiceForm.vue')
)
const UnsplashServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/UnsplashServiceForm.vue')
)
const TenorServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/TenorServiceForm.vue')
)
const OpenAIServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/OpenAIServiceForm.vue')
)
const BitlyServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/BitlyServiceForm.vue')
)
const YourlsServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/YourlsServiceForm.vue')
)
const ShlinkServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/ShlinkServiceForm.vue')
)
const AdobeExpressServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/AdobeExpressServiceForm.vue')
)
const PexelsServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/PexelsServiceForm.vue')
)
const GiphyServiceForm = defineAsyncComponent(
  () => import('@/Components/ServiceForm/GiphyServiceForm.vue')
)
const MastodonServiceManager = defineAsyncComponent(
  () => import('@/Components/ServiceForm/MastodonServiceManager.vue')
)

defineOptions({ layout: AdminLayout })

const props = defineProps({
  services: {
    type: Object,
    required: true
  },
  mastodonServers: {
    type: Array,
    default: () => []
  }
})

const form = useForm(props.services)

const tab = ref('facebook')
</script>
<template>
  <Head :title="$t('service.third_party_services')" />

  <div class="row-py w-full mx-auto">
    <PageHeader :title="$t('service.third_party_services')">
      <template #description>
        {{ $t('service.third_party_services_desc') }}
      </template>
    </PageHeader>

    <div class="w-full row-px mb-lg">
      <Tabs>
        <Tab :active="tab === 'facebook'" @click="tab = 'facebook'">
          <template #icon>
            <ProviderIcon provider="facebook" />
          </template>

          Facebook
        </Tab>

        <Tab :active="tab === 'threads'" @click="tab = 'threads'">
          <template #icon>
            <ProviderIcon provider="threads" />
          </template>

          Threads
        </Tab>

        <Tab :active="tab === 'bluesky'" @click="tab = 'bluesky'">
          <template #icon>
            <ProviderIcon provider="bluesky" />
          </template>

          Bluesky
        </Tab>

        <Tab :active="tab === 'google'" @click="tab = 'google'">
          <template #icon>
            <GoogleIcon class="text-google" />
          </template>
          Google
        </Tab>

        <Tab :active="tab === 'pinterest'" @click="tab = 'pinterest'">
          <template #icon>
            <ProviderIcon provider="pinterest" />
          </template>
          Pinterest
        </Tab>

        <Tab :active="tab === 'linkedin'" @click="tab = 'linkedin'">
          <template #icon>
            <ProviderIcon provider="linkedin" />
          </template>
          LinkedIn
        </Tab>

        <Tab :active="tab === 'tiktok'" @click="tab = 'tiktok'">
          <template #icon>
            <ProviderIcon provider="tiktok" />
          </template>
          TikTok
        </Tab>

        <Tab :active="tab === 'twitter'" @click="tab = 'twitter'">
          <template #icon>
            <ProviderIcon provider="twitter" />
          </template>
          X
        </Tab>

        <Tab :active="tab === 'mastodon'" @click="tab = 'mastodon'">
          <template #icon>
            <ProviderIcon provider="mastodon" />
          </template>
          Mastodon
        </Tab>

        <Tab :active="tab === 'unsplash'" @click="tab = 'unsplash'">
          <template #icon>
            <UnsplashIcon class="text-black" />
          </template>
          Unsplash
        </Tab>

        <Tab :active="tab === 'pexels'" @click="tab = 'pexels'">
          <template #icon>
            <PexelsIcon />
          </template>
          Pexels
        </Tab>

        <Tab :active="tab === 'tenor'" @click="tab = 'tenor'">
          <template #icon>
            <Tenor />
          </template>
          Tenor
        </Tab>

        <Tab :active="tab === 'giphy'" @click="tab = 'giphy'">
          <template #icon>
            <Giphy />
          </template>
          Giphy
        </Tab>

        <Tab :active="tab === 'adobe_express'" @click="tab = 'adobe_express'"> Adobe Express </Tab>

        <Tab :active="tab === 'openai'" @click="tab = 'openai'">
          <template #icon>
            <OpenAI class="text-openai" />
          </template>
          OpenAI
        </Tab>

        <Tab :active="tab === 'bitly'" @click="tab = 'bitly'">
          <template #icon>
            <Bitly />
          </template>
          Bitly
        </Tab>

        <Tab :active="tab === 'yourls'" @click="tab = 'yourls'">
          <template #icon>
            <Yourls />
          </template>
          Yourls
        </Tab>

        <Tab :active="tab === 'shlink'" @click="tab = 'shlink'">
          <template #icon>
            <Shlink />
          </template>
          Shlink
        </Tab>
      </Tabs>
    </div>

    <div class="row-px">
      <template v-if="tab === 'facebook'">
        <FacebookServiceForm :form="form.facebook" />
      </template>

      <template v-if="tab === 'threads'">
        <ThreadsServiceForm :form="form.threads" />
      </template>

      <template v-if="tab === 'bluesky'">
        <BlueskyServiceForm :form="form.bluesky" />
      </template>

      <template v-if="tab === 'google'">
        <GoogleServiceForm :form="form.google" />
      </template>

      <template v-if="tab === 'pinterest'">
        <PinterestServiceForm :form="form.pinterest" />
      </template>

      <template v-if="tab === 'twitter'">
        <TwitterServiceForm :form="form.twitter" />
      </template>

      <template v-if="tab === 'linkedin'">
        <LinkedInServiceForm :form="form.linkedin" />
      </template>

      <template v-if="tab === 'tiktok'">
        <TikTokServiceForm :form="form.tiktok" />
      </template>

      <template v-if="tab === 'mastodon'">
        <MastodonServiceManager :servers="mastodonServers" />
      </template>

      <template v-if="tab === 'unsplash'">
        <UnsplashServiceForm :form="form.unsplash" />
      </template>

      <template v-if="tab === 'pexels'">
        <PexelsServiceForm :form="form.pexels" />
      </template>

      <template v-if="tab === 'tenor'">
        <TenorServiceForm :form="form.tenor" />
      </template>

      <template v-if="tab === 'giphy'">
        <GiphyServiceForm :form="form.giphy" />
      </template>

      <template v-if="tab === 'adobe_express'">
        <AdobeExpressServiceForm :form="form.adobe_express" />
      </template>

      <template v-if="tab === 'openai'">
        <OpenAIServiceForm :form="form.openai" />
      </template>

      <template v-if="tab === 'bitly'">
        <BitlyServiceForm :form="form.bitly" />
      </template>

      <template v-if="tab === 'yourls'">
        <YourlsServiceForm :form="form.yourls" />
      </template>

      <template v-if="tab === 'shlink'">
        <ShlinkServiceForm :form="form.shlink" />
      </template>
    </div>
  </div>
</template>
