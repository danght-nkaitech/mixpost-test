<script setup>
import Select from '@/Components/Form/Select.vue'
import Input from '@/Components/Form/Input.vue'
import ProviderOptionWrap from '@/Components/ProviderVersionOptions/ProviderOptionWrap.vue'
import Label from '../Form/Label.vue'
import Checkbox from '@/Components/Form/Checkbox.vue'

defineProps({
  options: {
    type: Object,
    required: true
  }
})
</script>
<template>
  <ProviderOptionWrap
    :title="$t('service.provider_options', { provider: 'YouTube' })"
    provider="youtube"
  >
    <div>
      <div class="w-full mb-xs">
        <Input
          id="youtube_title"
          v-model="options.title"
          type="text"
          :placeholder="$t('service.youtube.title')"
        />
      </div>

      <div class="w-full mb-xs">
        <Label for="privacy-status">{{ $t('service.youtube.privacy_config') }}</Label>
        <Select id="privacy-status" v-model="options.status">
          <option value="public">{{ $t('service.youtube.public') }}</option>
          <option value="private">{{ $t('service.youtube.private') }}</option>
          <option value="unlisted">{{ $t('service.youtube.unlisted') }}</option>
        </Select>
      </div>

      <div class="w-full">
        <label>
          <Checkbox v-model:checked="options.made_for_kids" />
          {{ $t('service.youtube.made_for_kids') }}
        </label>
      </div>
    </div>
  </ProviderOptionWrap>
</template>
