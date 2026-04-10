<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import useHttpClient from '../../../Composables/useHttpClient'
import { useAPIForm } from '../../../Composables/useAPIForm'
import useEditor from '../../../Composables/useEditor'
import GeneratedContent from './GeneratedContent.vue'
import Preloader from './Preloader.vue'
import DraftContent from './DraftContent.vue'
import Commands from './Commands.vue'

const routePrefix = inject('routePrefix')
const workspaceCtx = inject('workspaceCtx')

defineEmits(['replace'])

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  command: {
    type: String,
    required: true
  },
  characterLimit: {
    type: Number,
    default: 1000
  }
})

const form = useAPIForm({
  text: props.text,
  command: props.command,
  character_limit:
    props.characterLimit === 0 || props.characterLimit > 1000 ? 1000 : props.characterLimit
})

const { getTextFromHtmlString } = useEditor()

const plainText = computed(() => {
  return getTextFromHtmlString(props.text)
})

const textExpanded = ref(false)
const generatedText = ref('')

const { onCatch } = useHttpClient()

onMounted(() => {
  modify()
})

const modify = () => {
  form.post(route(`${routePrefix}.ai.text.modify`, { workspace: workspaceCtx.id }), {
    onSuccess(response) {
      generatedText.value = response.text
    },
    onError(error) {
      onCatch(error)
    }
  })
}
</script>
<template>
  <div>
    <div class="font-medium">{{ $t('ai.draft_content') }}</div>
    <DraftContent :expanded="textExpanded" class="mt-xs" @click="textExpanded = !textExpanded"
      >{{ plainText }}
    </DraftContent>
  </div>

  <template v-if="form.processing">
    <Preloader />
  </template>

  <template v-if="generatedText && !form.processing">
    <div class="mt-lg">
      <div class="font-medium">{{ $t('ai.generated_content') }}</div>

      <GeneratedContent
        :text="generatedText"
        :insert-button-name="$t('general.replace')"
        class="mt-xs"
        @click-insert="$emit('replace', generatedText)"
        @click-retry="modify"
      />

      <Commands
        class="mt-lg"
        @selected-command="
          $event => {
            form.command = $event
            form.text = generatedText
            modify()
          }
        "
      />
    </div>
  </template>
</template>
