<script setup>
import { Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import Logo from '@/Components/DataDisplay/Logo.vue'
import MenuItem from '@/Components/Sidebar/MenuItem.vue'
import MenuDelimiter from '@/Components/Sidebar/MenuDelimiter.vue'
import MenuGroupHeader from '@/Components/Sidebar/MenuGroupHeader.vue'
import MenuGroupBody from '@/Components/Sidebar/MenuGroupBody.vue'
import UserMenu from '@/Components/Navigation/UserMenu.vue'
import GridIcon from '@/Icons/Grid.vue'
import UsersIcon from '@/Icons/Users.vue'
import Dashboard from '../../Icons/Dashboard.vue'
import RectangleStack from '../../Icons/RectangleStack.vue'
import Cog from '../../Icons/Cog.vue'
import Document from '../../Icons/Document.vue'

const routePrefix = inject('routePrefix')
</script>
<template>
  <div class="w-full h-full flex flex-col pb-2xl pt-20 bg-white border-r border-gray-200">
    <div class="absolute top-0 w-full text-center">
      <div class="bg-primary-50 border-b border-r border-gray-200 p-xs">
        <span class="text-black font-medium text-sm">{{ $t('panel.enterprise_console') }}</span>
      </div>
    </div>
    <div class="relative mb-12 px-xl">
      <Link :href="route(`${routePrefix}.dashboard`)">
        <Logo class="h-12" />
      </Link>
    </div>

    <div class="flex flex-col space-y-lg overflow-y-auto px-xl h-full">
      <MenuGroupBody>
        <MenuItem
          :url="route(`${routePrefix}.dashboard`)"
          :active="$page.component === 'Panel/Dashboard/Dashboard'"
        >
          <template #icon>
            <Dashboard />
          </template>
          {{ $t('sidebar.dashboard') }}
        </MenuItem>
        <MenuItem
          :url="route(`${routePrefix}.users.index`)"
          :active="$page.component === 'Panel/Users/Users'"
        >
          <template #icon>
            <UsersIcon />
          </template>
          {{ $t('user.users') }}
        </MenuItem>
      </MenuGroupBody>
      <MenuDelimiter />
      <MenuGroupHeader>
        {{ $t('sidebar.business') }}
      </MenuGroupHeader>
      <MenuGroupBody>
        <MenuItem
          :url="route(`${routePrefix}.workspaces.index`)"
          :active="$page.component === 'Panel/Workspaces/Workspaces'"
        >
          <template #icon>
            <GridIcon />
          </template>
          {{ $t('workspace.workspaces') }}
        </MenuItem>
        <MenuItem
          :url="route(`${routePrefix}.receipts.index`)"
          :active="$page.component === 'Panel/Receipts/Index'"
        >
          <template #icon>
            <Document />
          </template>
          {{ $t('sidebar.receipts') }}
        </MenuItem>
      </MenuGroupBody>
      <MenuDelimiter />
      <MenuGroupHeader>
        {{ $t('sidebar.configuration') }}
      </MenuGroupHeader>
      <MenuGroupBody>
        <MenuItem
          :url="route(`${routePrefix}.plans.index`)"
          :active="$page.component === 'Panel/Plans/Plans'"
        >
          <template #icon>
            <RectangleStack />
          </template>
          {{ $t('sidebar.plans') }}
        </MenuItem>
        <MenuItem
          :url="route(`${routePrefix}.configs.system.view`)"
          :active="$page.component === 'Panel/Configs/SystemConfig'"
        >
          <template #icon>
            <Cog />
          </template>
          {{ $t('general.settings') }}
        </MenuItem>
      </MenuGroupBody>
    </div>

    <div class="px-xl pt-xl">
      <UserMenu />
    </div>
  </div>
</template>
