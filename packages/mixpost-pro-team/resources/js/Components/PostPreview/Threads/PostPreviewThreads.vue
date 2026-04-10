<script setup>
import useEditor from '@/Composables/useEditor'
import Panel from '@/Components/Surface/Panel.vue'
import EditorReadOnly from '@/Components/Package/EditorReadOnly.vue'
import URLPreview from '../Threads/URLPreview.vue'
import defaultAvatar from '@/Components/PostPreview/Threads/assets/img/avatar.png'
import Likes from '../Instagram/Icons/Likes.vue'
import Comments from '../Instagram/Icons/Comments.vue'
import Share from '../Instagram/Icons/Share.vue'
import Flex from '../../Layout/Flex.vue'
import Gallery from '../Threads/Gallery.vue'

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
    type: [String, null]
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
      <div
        :class="{ 'thread pb-md': index !== props.content.length - 1 }"
        class="relative flex items-start"
      >
        <div class="mr-sm">
          <span class="inline-flex justify-center items-center shrink-0 w-10 h-10 rounded-full">
            <img
              :src="image || defaultAvatar"
              class="object-cover w-full h-full rounded-full z-10"
              alt=""
            />
          </span>
        </div>
        <div class="w-full">
          <div class="flex items-center">
            <div class="font-medium mr-xs">{{ name }}</div>
            <div class="text-gray-400">1m</div>
          </div>

          <EditorReadOnly
            :value="contentItem.body"
            :class="{
              'mt-xs': !isDocEmpty(contentItem.body),
              'h-0': isDocEmpty(contentItem.body),
              'mb-xs': contentItem.media.length
            }"
          />

          <template v-if="contentItem.url">
            <URLPreview :url="contentItem.url" class="mt-sm" />
          </template>

          <Gallery :media="contentItem.media" />

          <Flex gap="gap-lg" class="items-center mt-sm">
            <div>
              <Likes class="w-5! h-5!" />
            </div>
            <div>
              <Comments class="w-5! h-5!" />
            </div>
            <div>
              <Share class="w-5! h-5!" />
            </div>
          </Flex>
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
  background: rgb(229, 229, 229);
  z-index: 0;
}
</style>
