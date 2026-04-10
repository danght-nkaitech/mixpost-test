<script setup>
import { Link } from '@inertiajs/vue3'
import { inject } from 'vue'

const routePrefix = inject('routePrefix')

defineProps({
  uuid: {
    type: String,
    required: true
  },
  name: {
    type: String,
    required: true
  },
  path: {
    type: String,
    default: 'view'
  }
})
</script>
<template>
  <Link
    :href="route(`${routePrefix}.workspaces.${path}`, { workspace: uuid })"
    class="link-primary"
  >
    {{ name }}
  </Link>
</template>
