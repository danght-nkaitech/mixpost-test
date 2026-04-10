<script setup>
import Badge from '../DataDisplay/Badge.vue'
import Flex from '../Layout/Flex.vue'

defineProps({
  user: {
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
    :class="[{ [conditionalClass]: user.is_admin }]"
    class="sm:items-center"
  >
    <template v-if="user.is_admin">
      <Badge v-tooltip="$t('user.system_admin')" variant="warning" class="h-6">S </Badge>
    </template>
  </Flex>
</template>
