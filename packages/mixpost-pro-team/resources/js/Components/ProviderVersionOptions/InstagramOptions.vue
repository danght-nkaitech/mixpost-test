<script setup>
import Radio from '@/Components/Form/Radio.vue'
import ProviderOptionWrap from '@/Components/ProviderVersionOptions/ProviderOptionWrap.vue'
import { onMounted, watch } from 'vue'
import usePostMetaOptionsValidate from '../../Composables/usePostMetaOptionsValidate'

const props = defineProps({
  options: {
    type: Object,
    required: true
  },
  activeVersion: {
    type: Number,
    default: 0
  },
  versions: {
    type: Array,
    required: true
  }
})

const { postTypeDisabled, postReelDisabled, validatePostType } = usePostMetaOptionsValidate()

const handleValidate = () => {
  validatePostType({
    options: props.options,
    activeVersion: props.activeVersion,
    versions: props.versions
  })
}

const provider = 'instagram'

watch(props.versions, handleValidate)

onMounted(handleValidate)
</script>
<template>
  <ProviderOptionWrap
    :title="$t('service.provider_options', { provider: 'Instagram' })"
    :provider="provider"
  >
    <div>
      <div class="flex items-center space-x-sm">
        <label>
          <Radio v-model:checked="options.type" :disabled="postTypeDisabled" value="post" />
          {{ $t(`service.${provider}.post`) }}
        </label>
        <label>
          <Radio v-model:checked="options.type" :disabled="postReelDisabled" value="reel" />
          {{ $t(`service.${provider}.reel`) }}
        </label>
        <label>
          <Radio v-model:checked="options.type" value="story" />
          {{ $t(`service.${provider}.story`) }}
        </label>
      </div>
    </div>
  </ProviderOptionWrap>
</template>
