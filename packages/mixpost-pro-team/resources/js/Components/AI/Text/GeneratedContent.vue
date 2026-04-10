<script setup>
import { ref } from 'vue'
import Flex from '../../Layout/Flex.vue'
import PrimaryButton from '../../Button/PrimaryButton.vue'
import Refresh from '../../../Icons/Refresh.vue'
import SecondaryButton from '../../Button/SecondaryButton.vue'
import ClipboardButton from '../../Util/ClipboardButton.vue'

defineProps({
  text: {
    type: String,
    required: true
  },
  buttonsDisabled: {
    type: Boolean,
    default: false
  },
  insertButtonName: {
    type: String,
    default: ''
  }
})

defineEmits(['clickInsert', 'clickRetry'])

const dom = ref('')
</script>
<template>
  <div class="p-md rounded-md border border-primary-500">
    <p ref="dom">{{ text }}</p>

    <Flex class="mt-md justify-between items-center">
      <ClipboardButton
        v-tooltip="$t('system.copy')"
        :html-elm="dom"
        :show-text="false"
        size="xs"
        :disabled="buttonsDisabled"
        component="PureButton"
      />

      <Flex>
        <SecondaryButton size="xs" :disabled="buttonsDisabled" @click="$emit('clickRetry')">
          <template #icon>
            <Refresh />
          </template>
          {{ $t('general.retry') }}
        </SecondaryButton>

        <PrimaryButton size="sm" :disabled="buttonsDisabled" @click="$emit('clickInsert')">
          {{ insertButtonName ? insertButtonName : $t('general.insert') }}
        </PrimaryButton>
      </Flex>
    </Flex>
  </div>
</template>
