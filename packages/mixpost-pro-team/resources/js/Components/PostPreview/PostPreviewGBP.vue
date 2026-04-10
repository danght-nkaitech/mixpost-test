<script setup>
import Panel from '@/Components/Surface/Panel.vue'
import Flex from '@/Components/Layout/Flex.vue'
import Avatar from '@/Components/DataDisplay/Avatar.vue'
import { computed } from 'vue'
import EditorReadOnly from '@/Components/Package/EditorReadOnly.vue'
import TagIcon from '@/Icons/Tag.vue'
import useEditor from '@/Composables/useEditor.js'
import useSettings from '@/Composables/useSettings.js'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  name: {
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

const { t: $t } = useI18n()

const { locale } = useSettings()
const { isDocEmpty } = useEditor()

const contentItem = computed(() => {
  return props.content.length > 0 ? props.content[0] : null
})

const image = computed(() => {
  if (contentItem.value && contentItem.value.media.length > 0) {
    return contentItem.value.media[0].url
  }
  return null
})

const showCallToActionLink = computed(() => {
  if (props.options.type === 'offer') {
    return false // Offers have their own call-to-action section
  }
  return props.options.button !== 'NONE' && props.options.button_link
})

const callToActionButtonName = computed(() => {
  return (
    {
      BOOK: $t('service.gbp.book'),
      ORDER: $t('service.gbp.order_online'),
      SHOP: $t('service.gbp.buy'),
      LEARN_MORE: $t('service.gbp.learn_more'),
      SIGN_UP: $t('service.gbp.sign_up')
    }[props.options.button] || props.options.button
  )
})

const showCallToActionCallInfo = computed(() => {
  if (props.options.type === 'offer') {
    return false // Offers have their own call-to-action section
  }
  return props.options.button === 'CALL'
})

const formatDate = dateString => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString(locale, {
    month: 'short',
    day: 'numeric'
  })
}

const validityText = computed(() => {
  if (props.options.start_date && props.options.end_date) {
    return `${$t('service.gbp.offer_valid')} ${formatDate(props.options.start_date)} - ${formatDate(props.options.end_date)}`
  }
  return ''
})
</script>

<template>
  <Panel class="relative">
    <Flex gap="gap-sm" class="items-center">
      <Avatar :name="name" size="md" />
      <div class="font-medium">{{ name }}</div>
    </Flex>

    <div v-if="options.type === 'event'" class="mt-md">
      <div class="text-black text-xl">{{ options.event_title }}</div>
    </div>

    <Flex v-if="options.type === 'offer'" class="justify-between mt-md">
      <div>
        <div class="text-black text-xl">{{ options.event_title }}</div>
        <div class="text-base">{{ validityText }}</div>
      </div>
      <div class="text-orange-400">
        <TagIcon />
      </div>
    </Flex>

    <div v-if="image" class="bg-black w-full mt-md">
      <img :src="image" alt="" style="max-height: 305px" class="block mx-auto max-w-full" />
    </div>

    <template v-if="contentItem">
      <EditorReadOnly
        :value="contentItem.body"
        :class="{ 'mt-xs': !isDocEmpty(contentItem.body), 'mb-xs': contentItem.media.length }"
      />
    </template>

    <div v-if="options.type === 'offer' && options.offer_has_details" class="mt-md">
      <div
        v-if="options.coupon_code"
        class="text-center py-8 border-2 border-dashed border-gray-300 bg-gray-50"
      >
        <div class="text-gray-600 text-sm mb-2">{{ $t('service.gbp.show_code') }}</div>
        <div class="text-4xl font-bold text-black mb-4">{{ options.coupon_code }}</div>
        <div class="text-sm text-black font-medium">{{ validityText }}</div>
      </div>

      <div v-if="options.offer_link" class="mt-md">
        <a :href="options.offer_link" class="text-blue-600 text-sm font-medium hover:underline">
          {{ $t('service.gbp.redeem_online') }}
        </a>
      </div>

      <div v-if="options.terms" class="mt-md">
        <div class="text-gray-400 text-sm">
          {{ options.terms }}
        </div>
      </div>
    </div>

    <a
      v-if="showCallToActionLink"
      :href="options.button_link"
      class="block mt-md text-blue-600 text-sm font-medium hover:underline"
    >
      {{ callToActionButtonName }}
    </a>

    <div v-if="showCallToActionCallInfo" class="mt-md text-blue-600 text-sm font-medium">
      {{ $t('service.gbp.call_now') }}
    </div>
  </Panel>
</template>
