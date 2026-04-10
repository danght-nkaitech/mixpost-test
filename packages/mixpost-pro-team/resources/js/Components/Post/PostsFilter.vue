<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import Dropdown from '@/Components/Dropdown/Dropdown.vue'
import Tag from '@/Components/DataDisplay/Tag.vue'
import ProviderIcon from '@/Components/Account/ProviderIcon.vue'
import PureButton from '@/Components/Button/PureButton.vue'
import Badge from '@/Components/DataDisplay/Badge.vue'
import VerticallyScrollableContent from '@/Components/Surface/VerticallyScrollableContent.vue'
import FunnelIcon from '@/Icons/Funnel.vue'
import Checkbox from '@/Components//Form/Checkbox.vue'
import SearchInput from '../Util/SearchInput.vue'
import NoResult from '../Util/NoResult.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const accounts = computed(() => {
  return usePage().props.accounts
})

const tags = computed(() => {
  return usePage().props.tags
})

const total = computed(() => {
  return props.modelValue.tags.length + props.modelValue.accounts.length
})

const clear = () => {
  emit(
    'update:modelValue',
    Object.assign(props.modelValue, {
      keyword: '',
      tags: [],
      accounts: []
    })
  )
}
</script>
<template>
  <div class="flex items-center">
    <SearchInput v-model="modelValue.keyword" class="mr-xs rtl:mr-0 rtl:ml-xs" />

    <Dropdown width-classes="w-72" placement="bottom-end" :closeable-on-content="false">
      <template #trigger>
        <PrimaryButton size="md" :hidden-text-on-small-screen="true">
          <template #icon>
            <FunnelIcon />
          </template>
          <span
            >{{ $t('general.filters') }}
            <span v-if="total" class="px-2 py-1 rounded-md bg-white text-black font-bold">{{
              total
            }}</span></span
          >
        </PrimaryButton>
      </template>

      <template #header>
        <PureButton @click="clear">{{ $t('general.clear_filter') }}</PureButton>
      </template>

      <template #content>
        <VerticallyScrollableContent>
          <div class="p-sm">
            <div class="font-semibold">{{ $t('post.labels') }}</div>
            <div v-if="tags.length" class="mt-sm flex flex-wrap items-center gap-xs">
              <template v-for="tag in tags" :key="tag.id">
                <label class="flex items-center cursor-pointer">
                  <Checkbox v-model:checked="modelValue.tags" :value="tag.id" number class="mr-1" />
                  <Tag :item="tag" :removable="false" :editable="false" />
                </label>
              </template>
            </div>
            <div v-else>
              <NoResult class="mt-xs">{{ $t('tag.no_labels_found') }}</NoResult>
            </div>
          </div>

          <div class="p-sm mt-sm">
            <div class="font-semibold">{{ $t('account.accounts') }}</div>
            <div v-if="accounts.length" class="mt-sm flex flex-wrap items-center gap-xs">
              <template v-for="account in accounts" :key="account.id">
                <label class="flex items-center cursor-pointer">
                  <Checkbox
                    v-model:checked="modelValue.accounts"
                    :value="account.id"
                    number
                    class="mr-1"
                  />
                  <Badge>
                    <ProviderIcon :provider="account.provider" class="w-4! h-4! mr-xs" />
                    {{ account.name }}
                  </Badge>
                </label>
              </template>
            </div>
            <div v-else>
              <NoResult class="mt-xs">{{ $t('account.no_accounts_found') }}</NoResult>
            </div>
          </div>
        </VerticallyScrollableContent>
      </template>
    </Dropdown>
  </div>
</template>
