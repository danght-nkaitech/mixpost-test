<script setup>
import { ref } from 'vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import AdjustmentsHorizontal from '../../Icons/AdjustmentsHorizontal.vue'
import Select from '../Form/Select.vue'
import DialogModal from '../Modal/DialogModal.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import Input from '../Form/Input.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import useAdobeExpress from '../../Composables/useAdobeExpress.js'
import { toRawIfProxy } from '@/helpers.js'
import Logo from '@/Components/DataDisplay/Logo.vue'

const emit = defineEmits(['close', 'insert', 'selectMediaInMediaLibrary'])
const showCustomSizeModal = ref(false)

const allowOnlyDigits = e => {
  if (!/\d/.test(e.key)) {
    e.preventDefault()
  }
}

const { socialMediaSizes, customSize, openAdobeExpressEditor, selected } = useAdobeExpress(emit)

defineExpose({ selected })
</script>
<template>
  <div class="p-md bg-stone-500 w-auto h-auto rounded-lg">
    <div class="flex items-center mb-sm">
      <Logo class="mr-xs" />
      <span>{{ $t('media.create_for_adobe_express') }}</span>
    </div>
    <div class="flex flex-wrap relative">
      <SecondaryButton class="m-1 !py-xs" @click="showCustomSizeModal = true">
        <template #icon>
          <AdjustmentsHorizontal />
        </template>
        <span class="pl-1">{{ $t('media.custom_size') }}</span>
      </SecondaryButton>
      <SecondaryButton
        v-for="size in socialMediaSizes"
        :key="size.value"
        class="m-1"
        @click="openAdobeExpressEditor({ canvasSize: size.value })"
      >
        {{ size.name }}
      </SecondaryButton>
    </div>
  </div>

  <DialogModal
    :show="showCustomSizeModal"
    max-width="sm"
    :closeable="true"
    @close="showCustomSizeModal = false"
  >
    <template #header>
      {{ $t('media.custom_size') }}
    </template>
    <template #body>
      <VerticalGroup>
        <template #title>
          <label for="cs_width">{{ $t('media.width') }}</label>
        </template>
        <Input id="cs_width" v-model="customSize.width" type="number" @keypress="allowOnlyDigits" />
      </VerticalGroup>
      <VerticalGroup class="mt-sm">
        <template #title>
          <label for="cs_height">{{ $t('media.height') }}</label>
        </template>
        <Input
          id="cs_height"
          v-model="customSize.height"
          type="number"
          @keypress="allowOnlyDigits"
        />
      </VerticalGroup>
      <VerticalGroup class="mt-sm">
        <template #title>
          <label for="cs_unit">{{ $t('media.unit') }}</label>
        </template>
        <Select id="unit" v-model="customSize.unit">
          <option value="px">{{ $t('media.px') }}</option>
          <option value="in">{{ $t('media.inch') }}</option>
          <option value="mm">{{ $t('media.mm') }}</option>
        </Select>
      </VerticalGroup>
    </template>
    <template #footer>
      <SecondaryButton class="mr-xs rtl:mr-0 rtl:ml-xs" @click="showCustomSizeModal = false">
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <PrimaryButton
        @click="
          () => {
            showCustomSizeModal = false
            openAdobeExpressEditor({ canvasSize: toRawIfProxy(customSize) })
          }
        "
      >
        {{ $t('media.create_design') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
