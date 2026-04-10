<script setup>
import { provide, reactive, watchEffect } from 'vue'
import Panel from '@/Components/Surface/Panel.vue'
import Story from './Story.vue'
import Reel from './Reel.vue'
import Post from './Post.vue'
import AlertText from '../../Util/AlertText.vue'

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
  },
  options: {
    required: true,
    type: Object
  }
})

const context = reactive({
  name: props.name,
  username: props.username,
  image: props.image,
  content: props.content,
  options: props.options
})

watchEffect(() => {
  Object.keys(props).forEach(key => {
    context[key] = props[key]
  })
})

provide('instagramCtx', context)
</script>
<template>
  <Panel class="relative">
    <template v-if="content[0].media.length">
      <template v-if="options.type === 'reel'">
        <Reel />
      </template>

      <template v-else-if="options.type === 'story'">
        <Story />
      </template>

      <div v-else>
        <Post />
      </div>
    </template>

    <template v-else>
      <AlertText variant="warning">
        {{ $t('service.instagram.select_video_image') }}
      </AlertText>
    </template>
  </Panel>
</template>
