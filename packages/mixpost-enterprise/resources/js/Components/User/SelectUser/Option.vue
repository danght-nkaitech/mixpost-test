<script setup>
import Flex from '../../Layout/Flex.vue'
import Avatar from '../../DataDisplay/Avatar.vue'

defineProps({
  option: {
    type: Object,
    required: true
  }
})
</script>
<template>
  <Flex class="items-center">
    <div>
      <Avatar :name="option.label" size="md" />
    </div>
    <div>
      <span class="block">{{ option.label }}</span>
      <span class="block user-email">{{ option.email }}</span>
    </div>
  </Flex>
</template>
<style lang="css">
@reference "@css/app.css";
.vs__selected .user-email {
  @apply text-gray-500;
}

.vs__dropdown-option .user-email {
  @apply text-gray-500;
}

.vs__dropdown-option--highlight .user-email {
  @apply text-gray-200;
}
</style>
