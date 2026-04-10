<script setup>
import { STATUS_ACTIVE, STATUS_PAST_DUE, STATUS_CANCELED } from '@/Constants/Subscription.js'
import { computed } from 'vue'
import SearchInput from '../Util/SearchInput.vue'
import Flex from '../Layout/Flex.vue'
import Tabs from '../Navigation/Tabs.vue'
import Tab from '../Navigation/Tab.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import Funnel from '../../Icons/Funnel.vue'
import PureButton from '../Button/PureButton.vue'
import VerticallyScrollableContent from '../Surface/VerticallyScrollableContent.vue'
import Checkbox from '../Form/Checkbox.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const total = computed(() => {
  return props.modelValue.access_status ? props.modelValue.access_status.length : 0
})

const changeSubscription = subscription_status => {
  emit(
    'update:modelValue',
    Object.assign(props.modelValue, {
      subscription_status,
      free: null
    })
  )
}

const setFreeAccess = () => {
  emit(
    'update:modelValue',
    Object.assign(props.modelValue, {
      subscription_status: null,
      free: true
    })
  )
}

const clear = () => {
  emit(
    'update:modelValue',
    Object.assign(props.modelValue, {
      keyword: '',
      access_status: []
    })
  )
}
</script>
<template>
  <Flex class="items-start md:items-center mt-lg md:mt-0">
    <Tabs class="mr-lg rtl:mr-0 rtl:ml-lg">
      <Tab
        :active="!modelValue.subscription_status && !modelValue.free"
        @click="changeSubscription(null)"
      >
        {{ $t('general.all') }}
      </Tab>

      <Tab :active="modelValue.free !== null" @click="setFreeAccess">
        {{ $t('general.free') }}
      </Tab>

      <Tab
        :active="modelValue.subscription_status === STATUS_ACTIVE"
        @click="changeSubscription(STATUS_ACTIVE)"
      >
        {{ $t('general.active') }}
      </Tab>

      <Tab
        :active="modelValue.subscription_status === STATUS_PAST_DUE"
        @click="changeSubscription(STATUS_PAST_DUE)"
      >
        {{ $t('general.due') }}
      </Tab>

      <Tab
        :active="modelValue.subscription_status === STATUS_CANCELED"
        @click="changeSubscription(STATUS_CANCELED)"
      >
        {{ $t('general.canceled') }}
      </Tab>
    </Tabs>

    <SearchInput v-model="modelValue.keyword" :placeholder="$t('general.search')" />

    <Dropdown width-classes="w-72" placement="bottom-end" :closeable-on-content="false">
      <template #trigger>
        <PrimaryButton size="md" :hidden-text-on-small-screen="true">
          <template #icon>
            <Funnel />
          </template>
          <span>
            Filters
            <span v-if="total" class="px-2 py-1 rounded-md bg-white text-black font-bold"
              >{{ total }}
            </span></span
          >
        </PrimaryButton>
      </template>

      <template #header>
        <PureButton @click="clear"> {{ $t('general.clear_filter') }}</PureButton>
      </template>

      <template #content>
        <VerticallyScrollableContent>
          <div class="p-sm">
            <VerticalGroup>
              <template #title>
                {{ $t('general.access_status') }}
              </template>

              <Flex :col="true">
                <label>
                  <Checkbox v-model:checked="modelValue.access_status" value="subscription" />
                  {{ $t('subscription.requires_subscription') }}
                </label>

                <label>
                  <Checkbox v-model:checked="modelValue.access_status" value="unlimited" />
                  {{ $t('workspace.unlimited') }}
                </label>

                <label>
                  <Checkbox v-model:checked="modelValue.access_status" value="locked" />
                  {{ $t('workspace.locked') }}
                </label>
              </Flex>
            </VerticalGroup>
          </div>
        </VerticallyScrollableContent>
      </template>
    </Dropdown>
  </Flex>
</template>
