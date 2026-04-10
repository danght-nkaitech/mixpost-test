<script setup>
import { ACCESS_STATUS_UNLIMITED, ACCESS_STATUS_LOCKED } from '../../Constants/Workspace'
import LockClosed from '../../Icons/LockClosed.vue'
import Badge from '../DataDisplay/Badge.vue'
import Flex from '../Layout/Flex.vue'

defineProps({
  workspace: {
    type: Object,
    required: true
  },
  conditionalClass: {
    type: String,
    default: ''
  }
})
</script>
<template>
  <Flex
    :responsive="false"
    :class="[
      {
        [conditionalClass]:
          workspace.access_status === ACCESS_STATUS_UNLIMITED ||
          workspace.access_status === ACCESS_STATUS_LOCKED
      }
    ]"
    class="sm:items-center"
  >
    <template v-if="workspace.access_status === ACCESS_STATUS_UNLIMITED">
      <Badge v-tooltip="$t('plan.unlimited_access')" variant="success" class="h-6">U </Badge>
    </template>

    <template v-if="workspace.access_status === ACCESS_STATUS_LOCKED">
      <Badge v-tooltip="$t('workspace.locked')" variant="error" class="h-6">
        <LockClosed class="w-[18px]! h-[18px]!" />
      </Badge>
    </template>
  </Flex>
</template>
