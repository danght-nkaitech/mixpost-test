<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import Clipboard from '../../Util/Clipboard'
import ClipboardIcon from '@/Icons/Clipboard.vue'
import CheckIcon from '@/Icons/Check.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import PureButton from '@/Components/Button/PureButton.vue'

const { t: $t } = useI18n()

const props = defineProps({
  component: {
    type: String,
    default: 'PrimaryButton'
  },
  componentSize: {
    type: String,
    default: 'md'
  },
  showText: {
    type: Boolean,
    default: true
  },
  htmlElm: {
    type: [Object, String],
    required: true
  },
  successMessage: {
    type: String,
    default: ''
  },
  errorMessage: {
    type: String,
    default: ''
  }
})

const copied = ref(null)

const components = {
  PrimaryButton,
  PureButton
}

const copy = () => {
  Clipboard.setSuccessMessage(
    props.successMessage ? props.successMessage : $t('system.copied_clipboard')
  )
    .setErrorMessage(props.errorMessage ? props.errorMessage : $t('system.failed_copied_clipboard'))
    .copy(props.htmlElm.innerText)
    .then(() => {
      copied.value = true
      setTimeout(() => {
        copied.value = null
      }, 2000)
    })
    .catch(() => {
      copied.value = false
    })
}
</script>
<template>
  <component :is="components[component]" :size="componentSize" @click="copy">
    <template #icon>
      <ClipboardIcon v-if="copied === null" />
      <CheckIcon v-if="copied === true" />
    </template>

    <span v-if="$slots.default">
      <slot />
    </span>
    <span v-if="!$slots.default && showText">
      {{ $t('system.copy') }}
    </span>
  </component>
</template>
