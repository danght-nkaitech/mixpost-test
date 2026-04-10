<script setup>
import { Link } from '@inertiajs/vue3'
import ExclamationCircle from '@/Icons/ExclamationCircle.vue'
import useSettings from '@/Composables/useSettings.js'

const { timeZone } = useSettings()
</script>

<template>
  <div class="text-sm flex items-center">
    <div class="mr-xs rtl:mr-0 rtl:ml-xs">{{ timeZone }}</div>

    <Link
      v-tooltip="$t('posting_schedule.posting_times_determined')"
      :href="route('mixpost.profile.index')"
    >
      <ExclamationCircle class="w-4! h-4!" />
    </Link>
  </div>
</template>
