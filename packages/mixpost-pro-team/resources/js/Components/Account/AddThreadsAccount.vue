<script setup>
import { inject } from 'vue'
import { Link } from '@inertiajs/vue3'
import ThreadsIcon from '@/Icons/Threads.vue'

const workspaceCtx = inject('workspaceCtx')
</script>
<template>
  <Link
    :href="route('mixpost.accounts.add', { workspace: workspaceCtx.id, provider: 'threads' })"
    method="post"
    as="button"
    type="button"
    class="w-full flex items-center px-lg py-4 hover:bg-threads/20 ease-in-out duration-200"
  >
    <span class="flex mr-4">
      <ThreadsIcon class="text-threads" />
    </span>
    <span class="flex flex-col items-start">
      <span class="font-medium">Threads</span>
      <span>{{ $t('service.threads.connect') }}</span>
    </span>
  </Link>
</template>
