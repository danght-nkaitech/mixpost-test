<script setup>
import { inject } from 'vue'
import { Link } from '@inertiajs/vue3'
import PageHeader from '../../Components/DataDisplay/PageHeader.vue'
import Tabs from '../../Components/Navigation/Tabs.vue'
import Tab from '../../Components/Navigation/Tab.vue'

const routePrefix = inject('routePrefix')
</script>
<template>
  <div class="row-py w-full mx-auto">
    <PageHeader :title="$t('general.settings')" />

    <div class="row-px">
      <Tabs class="overflow-x-auto flex-nowrap! md:flex-wrap! md:gap-sm max-w-full w-full mb-lg">
        <Link :href="route(`${routePrefix}.configs.general.form`)" class="mr-md">
          <Tab :active="$page.component === 'Admin/Configs/GeneralConfig'">{{
            $t('general.general')
          }}</Tab>
        </Link>

        <Link :href="route(`${routePrefix}.configs.theme.form`)" class="mr-md">
          <Tab :active="$page.component === 'Admin/Configs/ThemeConfig'">{{
            $t('theme.customization')
          }}</Tab>
        </Link>

        <Link :href="route(`${routePrefix}.configs.ai.form`)" class="mr-md">
          <Tab :active="$page.component === 'Admin/Configs/AIConfig'">{{ $t('ai.ai') }}</Tab>
        </Link>

        <Link :href="route(`${routePrefix}.configs.media.form`)" class="mr-md">
          <Tab :active="$page.component === 'Admin/Configs/MediaConfig'">Media</Tab>
        </Link>

        <Link :href="route(`${routePrefix}.configs.gif.form`)" class="mr-md">
          <Tab :active="$page.component === 'Admin/Configs/GifConfig'">GIFs</Tab>
        </Link>
      </Tabs>

      <slot />
    </div>
  </div>
</template>
