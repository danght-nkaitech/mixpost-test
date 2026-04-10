<script setup>
import Radio from '@/Components/Form/Radio.vue'
import ProviderOptionWrap from '@/Components/ProviderVersionOptions/ProviderOptionWrap.vue'

defineProps({
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

const provider = 'facebook_page'
</script>
<template>
  <ProviderOptionWrap :title="$t('service.facebook.page_options')" :provider="provider">
    <div>
      <div class="flex items-center space-x-sm">
        <label>
          <Radio v-model:checked="options.type" value="post" />
          {{ $t(`service.${provider}.post`) }}
        </label>
        <label>
          <Radio v-model:checked="options.type" value="reel" />
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
