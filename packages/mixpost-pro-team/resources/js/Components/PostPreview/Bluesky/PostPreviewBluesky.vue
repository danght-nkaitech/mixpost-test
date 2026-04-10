<script setup>
import { computed } from 'vue'
import useEditor from '@/Composables/useEditor'
import Bluesky from '@/SocialProviders/Bluesky'
import Panel from '@/Components/Surface/Panel.vue'
import Gallery from '@/Components/PostPreview/Bluesky/Gallery/Index.vue'
import EditorReadOnly from '@/Components/Package/EditorReadOnly.vue'
import URLPreview from '@/Components/PostPreview/Bluesky/URLPreview.vue'

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

const URL_ANCHOR_REGEX =
  /<a([^>]*href=["'])(https?:\/\/[^"']+)(["'][^>]*)>(\s*)(https?:\/\/[^<]+)(\s*)<\/a>/gi

const shortenUrlsInContent = content => {
  if (!content) return content

  // Replace the text content of anchor tags with shortened URLs, but keep the full URL in href
  return content.replace(
    URL_ANCHOR_REGEX,
    (_match, beforeHref, hrefUrl, afterHref, spaceBefore, textUrl, spaceAfter) => {
      const shortenedUrl = Bluesky.shortenUrl(textUrl)
      return `<a${beforeHref}${hrefUrl}${afterHref}>${spaceBefore}${shortenedUrl}${spaceAfter}</a>`
    }
  )
}

const processedContent = computed(() => {
  if (!props.content?.length) return []

  return props.content.map(item => ({
    ...item,
    body: shortenUrlsInContent(item.body)
  }))
})
</script>
<template>
  <Panel class="relative">
    <template v-for="(contentItem, index) in processedContent" :key="index">
      <div
        :class="{ 'thread pb-md': index !== processedContent.length - 1 }"
        class="relative flex items-start"
      >
        <div class="mr-sm">
          <span class="inline-flex justify-center items-center shrink-0 w-10 h-10 rounded-full">
            <img :src="image" class="object-cover w-full h-full rounded-full z-10" alt="" />
          </span>
        </div>
        <div class="w-full">
          <div>
            <div class="flex flex-wrap grow-0 space-x-1 overflow-hidden">
              <div class="font-medium mr-xs">{{ name }}</div>
              <div class="text-gray-400">@{{ username }}</div>
            </div>
          </div>

          <EditorReadOnly
            :value="contentItem.body"
            :class="{ 'mt-xs': !isDocEmpty(contentItem.body), 'mb-xs': contentItem.media.length }"
          />

          <template v-if="contentItem.url">
            <URLPreview :url="contentItem.url" class="mt-sm" />
          </template>

          <Gallery :media="contentItem.media" />

          <div class="mt-sm flex items-center justify-between">
            <div class="flex items-start flex-1">
              <svg
                fill="none"
                width="18"
                viewBox="0 0 24 24"
                height="18"
                style="color: rgb(111, 134, 159); pointer-events: none"
              >
                <path
                  fill="hsl(211, 20%, 53%)"
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2.002 6a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H12.28l-4.762 2.858A1 1 0 0 1 6.002 21v-2h-1a3 3 0 0 1-3-3V6Zm3-1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h2a1 1 0 0 1 1 1v1.234l3.486-2.092a1 1 0 0 1 .514-.142h7a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-14Z"
                ></path>
              </svg>
            </div>
            <div class="flex items-start flex-1">
              <svg
                fill="none"
                width="18"
                viewBox="0 0 24 24"
                height="18"
                style="color: rgb(111, 134, 159)"
              >
                <path
                  fill="hsl(211, 20%, 53%)"
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M17.957 2.293a1 1 0 1 0-1.414 1.414L17.836 5H6a3 3 0 0 0-3 3v3a1 1 0 1 0 2 0V8a1 1 0 0 1 1-1h11.836l-1.293 1.293a1 1 0 0 0 1.414 1.414l2.47-2.47a1.75 1.75 0 0 0 0-2.474l-2.47-2.47ZM20 12a1 1 0 0 1 1 1v3a3 3 0 0 1-3 3H6.164l1.293 1.293a1 1 0 1 1-1.414 1.414l-2.47-2.47a1.75 1.75 0 0 1 0-2.474l2.47-2.47a1 1 0 0 1 1.414 1.414L6.164 17H18a1 1 0 0 0 1-1v-3a1 1 0 0 1 1-1Z"
                ></path>
              </svg>
            </div>
            <div class="flex items-start flex-1">
              <svg
                fill="none"
                width="18"
                viewBox="0 0 24 24"
                height="18"
                style="color: rgb(111, 134, 159); pointer-events: none"
              >
                <path
                  fill="hsl(211, 20%, 53%)"
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M16.734 5.091c-1.238-.276-2.708.047-4.022 1.38a1 1 0 0 1-1.424 0C9.974 5.137 8.504 4.814 7.266 5.09c-1.263.282-2.379 1.206-2.92 2.556C3.33 10.18 4.252 14.84 12 19.348c7.747-4.508 8.67-9.168 7.654-11.7-.541-1.351-1.657-2.275-2.92-2.557Zm4.777 1.812c1.604 4-.494 9.69-9.022 14.47a1 1 0 0 1-.978 0C2.983 16.592.885 10.902 2.49 6.902c.779-1.942 2.414-3.334 4.342-3.764 1.697-.378 3.552.003 5.169 1.286 1.617-1.283 3.472-1.664 5.17-1.286 1.927.43 3.562 1.822 4.34 3.764Z"
                ></path>
              </svg>
            </div>
            <div class="flex items-start flex-1">
              <svg
                fill="none"
                viewBox="0 0 24 24"
                width="20"
                height="20"
                style="pointer-events: none"
              >
                <path
                  fill="hsl(211, 20%, 53%)"
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2 12a2 2 0 1 1 4 0 2 2 0 0 1-4 0Zm16 0a2 2 0 1 1 4 0 2 2 0 0 1-4 0Zm-6-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"
                ></path>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </template>
  </Panel>
</template>
<style scoped>
.thread::after {
  content: '';
  position: absolute;
  left: 1.2em;
  width: 2px;
  height: 100%;
  background: rgb(207, 217, 222);
  z-index: 0;
}
</style>
