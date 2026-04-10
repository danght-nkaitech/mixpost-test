<script setup>
import useEditor from '@/Composables/useEditor'
import Panel from '@/Components/Surface/Panel.vue'
import Gallery from '@/Components/ProviderGallery/Mastodon/MastodonGallery.vue'
import EditorReadOnly from '@/Components/Package/EditorReadOnly.vue'
import GlobeImg from '@img/social-icons/mastodon/globe.svg'
import ReplyImg from '@img/social-icons/mastodon/reply.svg'
import RetweetImg from '@img/social-icons/mastodon/retweet.svg'
import StarImg from '@img/social-icons/mastodon/star.svg'
import BookmarkImg from '@img/social-icons/mastodon/bookmark.svg'
import EllipsisImg from '@img/social-icons/mastodon/ellipsis.svg'

const props = defineProps({
  name: {
    required: true,
    type: String
  },
  username: {
    required: true,
    type: String
  },
  image: {
    required: true,
    type: String
  },
  content: {
    required: true,
    type: Array
  }
})

const { isDocEmpty } = useEditor()
</script>
<template>
  <Panel class="relative">
    <template v-for="(contentItem, index) in props.content" :key="index">
      <div class="relative">
        <div :class="{ 'pt-xl': index !== 0 }">
          <template v-if="index !== 0">
            <div
              :class="{ 'h-full!': index !== props.content.length - 1 }"
              class="status-line h-3 top-0 start-[21px] border-l border-gray-200 after:bg-gray-200 after:content-[''] after:block after:h-[54px] after:absolute after:top-[12px] after:w-[1px] after:start-[-1px] absolute w-0"
            ></div>
          </template>

          <div class="flex items-start justify-between">
            <div class="flex items-center">
              <div class="mr-sm">
                <span
                  class="inline-flex justify-center items-center shrink-0 w-10 h-10 rounded-full"
                >
                  <img :src="image" class="object-cover w-full h-full rounded-sm z-10" alt="" />
                </span>
              </div>
              <div>
                <div class="font-medium mr-xs">{{ name }}</div>
                <div class="text-gray-400">@{{ username }}</div>
              </div>
            </div>
            <div class="flex items-center">
              <span class="mr-xs">
                <img :src="GlobeImg" alt="Globe" class="w-4 h-4" />
              </span>
              <span class="text-gray-400 text-sm">19h</span>
            </div>
          </div>

          <div class="w-full">
            <div :class="{ 'pl-[3.3rem]': index !== 0 }">
              <EditorReadOnly
                :value="contentItem.body"
                :class="{
                  'mt-xs': !isDocEmpty(contentItem.body),
                  'mb-xs': contentItem.media.length
                }"
              />

              <Gallery :media="contentItem.media" />
            </div>

            <div
              :class="{
                'border-t border-b py-sm border-gray-200': index === 0 && props.content.length > 1,
                'pl-[3.3rem]': index !== 0
              }"
              class="mt-md flex items-center justify-between"
            >
              <div class="flex items-center">
                <img :src="ReplyImg" alt="Reply" class="w-5 h-5" />
                <div class="ml-xs">0</div>
              </div>
              <div class="flex items-center">
                <img :src="RetweetImg" alt="Retweet" class="w-5 h-5" />
              </div>
              <div class="flex items-center">
                <img :src="StarImg" alt="Star" class="w-5 h-5" />
              </div>
              <div class="flex items-center">
                <img :src="BookmarkImg" alt="Bookmark" class="w-5 h-5" />
              </div>
              <div class="flex items-center">
                <img :src="EllipsisImg" alt="Ellipsis" class="w-5 h-5" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </Panel>
</template>
