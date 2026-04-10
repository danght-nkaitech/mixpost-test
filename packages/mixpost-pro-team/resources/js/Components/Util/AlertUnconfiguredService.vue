<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import useAuth from '../../Composables/useAuth'
import Alert from './Alert.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'

const props = defineProps({
  isConfigured: {
    type: Object,
    required: true
  }
})

const { user } = useAuth()

const any = computed(() => {
  return Object.keys(props.isConfigured).some(service => {
    return !props.isConfigured[service]
  })
})
</script>
<template>
  <div v-if="user.is_admin && any" class="mb-md">
    <Alert variant="warning" :closeable="false" class="mb-md">
      <p v-if="!isConfigured.google">
        {{ $t('service.not_configured_service', { service: 'Google' }) }}
      </p>
      <p v-if="!isConfigured.facebook">
        {{ $t('service.not_configured_service', { service: 'Facebook' }) }}
      </p>
      <p v-if="!isConfigured.pinterest">
        {{ $t('service.not_configured_service', { service: 'Pinterest' }) }}
      </p>
      <p v-if="!isConfigured.linkedin">
        {{ $t('service.not_configured_service', { service: 'LinkedIn' }) }}
      </p>
      <p v-if="!isConfigured.twitter">
        {{ $t('service.not_configured_service', { service: 'X' }) }}
      </p>
      <p v-if="!isConfigured.tiktok">
        {{ $t('service.not_configured_service', { service: 'TikTok' }) }}
      </p>
      <p class="mt-xs italic">{{ $t('service.configure_services_desc') }}</p>
    </Alert>

    <Link :href="route('mixpost.services.index')" class="inline-block">
      <PrimaryButton>{{ $t('service.configure_services') }}</PrimaryButton>
    </Link>
  </div>
</template>
