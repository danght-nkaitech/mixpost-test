<script setup>
import { defineAsyncComponent } from 'vue'
import EmojiPicker from '@/Components/Package/EmojiPicker.vue'
import Flex from '../Layout/Flex.vue'
const AIAssist = defineAsyncComponent(() => import('@/Components/AI/Text/AIAssist.vue'))

defineProps({
  editor: {
    required: true,
    type: Object
  },
  text: {
    required: false,
    type: String
  }
})
</script>
<template>
  <Flex>
    <EmojiPicker @selected="emoji => editor.commands.insertContent(emoji.native)" />

    <template v-if="$page.props.ai_is_ready_to_use">
      <AIAssist
        :text="text"
        @insert="value => editor.commands.insertContent(value)"
        @replace="
          value => {
            editor.commands.clearContent()
            editor.commands.insertContent(value)
          }
        "
      />
    </template>
  </Flex>
</template>
