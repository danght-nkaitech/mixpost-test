<script setup>
import { Head } from '@inertiajs/vue3'
import { inject } from 'vue'
import MinimalLayout from '@/Layouts/Minimal.vue'
import Panel from '@/Components/Surface/Panel.vue'
import PrimaryButton from '../../Components/Button/PrimaryButton.vue'

defineOptions({ layout: MinimalLayout })

const routePrefix = inject('routePrefix')
</script>
<template>
  <Head :title="$page.props.title" />

  <Panel>
    <template #title>{{ $page.props.title }}</template>

    {{ $page.props.text }}
  </Panel>

  <div class="flex">
    <a :href="route(`${routePrefix}.dashboard`)" class="mt-lg">
      <PrimaryButton>{{ $t('dashboard.go_home') }}</PrimaryButton>
    </a>
  </div>
</template>
