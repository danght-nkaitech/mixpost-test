<script setup>
import TypeLayout from './TypeLayout.vue'

defineProps({
  item: {
    required: true,
    type: Object
  }
})
</script>
<template>
  <TypeLayout>
    <template #default>
      <template v-if="item.user">
        {{ $t('post_activity.user_set_drafts', { user: item.user.name }) }}
      </template>
      <template v-else>
        {{ $t('post_activity.set_drafts') }}
      </template>
    </template>
    <template #created_at>
      {{ item.timestamps.localized.created_at }}
    </template>
  </TypeLayout>
</template>
