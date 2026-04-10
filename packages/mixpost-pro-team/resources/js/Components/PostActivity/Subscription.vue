<script setup>
import { inject } from 'vue'
import Bell from '../../Icons/Bell.vue'
import BellSlash from '../../Icons/BellSlash.vue'
import PureButton from '../Button/PureButton.vue'
import Dropdown from '@/Components/Dropdown/Dropdown.vue'
import DropdownItem from '@/Components/Dropdown/DropdownItem.vue'
import Flex from '../Layout/Flex.vue'
import MenuDelimiter from '../Sidebar/MenuDelimiter.vue'

const workspaceCtx = inject('workspaceCtx')
</script>
<template>
  <div class="absolute right-0 top-0 mt-sm mr-sm md:mr-xl">
    <Dropdown width-classes="w-96" placement="bottom-end" :closeable-on-content="false">
      <template #trigger>
        <PureButton
          :disabled="$page.props.post === null"
          :class="{ 'text-primary-500': $page.props.has_activities_ns }"
        >
          <Bell />
        </PureButton>
      </template>

      <template #content>
        <DropdownItem
          :href="
            route('mixpost.posts.activities.subscribe', {
              workspace: workspaceCtx.id,
              post: $page.props.post.id
            })
          "
          link-method="post"
          link-as="button"
          :is-active="$page.props.has_activities_ns === true"
        >
          <template #icon>
            <Bell />
          </template>
          <Flex :col="true" class="items-start text-left">
            <span>{{ $t('post_activity.watch') }}</span>
            <span class="text-sm">{{ $t('post_activity.notify_all_activity') }}</span>
          </Flex>
        </DropdownItem>

        <MenuDelimiter />

        <DropdownItem
          :href="
            route('mixpost.posts.activities.unsubscribe', {
              workspace: workspaceCtx.id,
              post: $page.props.post.id
            })
          "
          link-method="delete"
          link-as="button"
          :is-active="!$page.props.has_activities_ns"
        >
          <template #icon>
            <BellSlash />
          </template>
          <Flex :col="true" class="items-start text-left">
            <span>{{ $t('post_activity.unwatch') }}</span>
            <span class="text-sm">{{ $t('post_activity.notify_mentions_only') }}</span>
          </Flex>
        </DropdownItem>
      </template>
    </Dropdown>
  </div>
</template>
