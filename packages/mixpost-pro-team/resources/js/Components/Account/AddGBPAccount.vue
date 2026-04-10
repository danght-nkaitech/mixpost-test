<script setup>
import { inject } from 'vue'
import { Link } from '@inertiajs/vue3'
import GBPIcon from '@/Icons/GBP.vue'

const workspaceCtx = inject('workspaceCtx')
</script>
<template>
  <Link
    :href="route('mixpost.accounts.add', { workspace: workspaceCtx.id, provider: 'gbp' })"
    method="post"
    as="button"
    type="button"
    class="w-full flex items-center px-lg py-4 hover:bg-gbp/20 ease-in-out duration-200"
  >
    <span class="flex mr-sm">
      <GBPIcon />
    </span>
    <span class="flex flex-col items-start">
      <span class="font-medium">Google Business Profile</span>
      <span>{{ $t('service.gbp.connect_profile') }}</span>
    </span>
  </Link>
</template>
