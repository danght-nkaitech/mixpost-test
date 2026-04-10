<script setup>
import { Link } from '@inertiajs/vue3'
import SecondaryButton from '../Button/SecondaryButton.vue'
import ArrowLeft from '../../Icons/ArrowLeft.vue'

defineProps({
  href: {
    type: String,
    default: route('mixpost.home')
  }
})
</script>
<template>
  <Link :href="href" class="flex items-center link">
    <SecondaryButton :hidden-text-on-small-screen="true" size="md">
      <template #icon>
        <ArrowLeft />
      </template>

      {{ $t('general.go_back') }}
    </SecondaryButton>
  </Link>
</template>
