<script setup>
import { Link } from '@inertiajs/vue3'
import { inject } from 'vue'

const routePrefix = inject('routePrefix')

defineProps({
  id: {
    type: Number,
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
  <Link :href="route(`${routePrefix}.users.${path}`, { user: id })" class="link-primary">
    {{ name }}
  </Link>
</template>
