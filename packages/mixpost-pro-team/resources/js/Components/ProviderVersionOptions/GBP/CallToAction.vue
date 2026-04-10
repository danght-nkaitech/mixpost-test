<script setup>
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Label from '@/Components/Form/Label.vue'
import Select from '@/Components/Form/Select.vue'
import Input from '@/Components/Form/Input.vue'

const props = defineProps({
  options: {
    type: Object,
    required: true
  }
})

const clearButtonLink = () => {
  if (props.options.button === 'CALL') {
    props.options.button_link = ''
  }
}
</script>

<template>
  <HorizontalGroup>
    <template #title>
      <Label for="button">{{ $t('service.gbp.button') }}</Label>
    </template>

    <Select id="button" v-model="options.button" @change="clearButtonLink">
      <option value="NONE">{{ $t('service.gbp.none') }}</option>
      <option value="BOOK">{{ $t('service.gbp.book') }}</option>
      <option value="ORDER">{{ $t('service.gbp.order_online') }}</option>
      <option value="SHOP">{{ $t('service.gbp.buy') }}</option>
      <option value="LEARN_MORE">{{ $t('service.gbp.learn_more') }}</option>
      <option value="SIGN_UP">{{ $t('service.gbp.sign_up') }}</option>
      <option value="CALL">{{ $t('service.gbp.call_now') }}</option>
    </Select>
  </HorizontalGroup>

  <template v-if="options.button !== 'NONE' && options.button !== 'CALL'">
    <HorizontalGroup class="mt-md md:mt-xs">
      <template #title>
        <Label for="button_link">{{ $t('service.gbp.button_link') }}</Label>
      </template>

      <Input id="button_link" v-model="options.button_link" placeholder="https://example.com" />
    </HorizontalGroup>
  </template>
</template>
