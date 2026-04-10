<script setup>
import DefaultLayout from '@/Layouts/Default.vue'
import Sidebar from '@/Components/Sidebar/SidebarPanel.vue'
import Navigation from '@/Components/Navigation/NavBar.vue'
</script>
<template>
  <DefaultLayout>
    <template #sidebar>
      <Sidebar />
    </template>

    <template #navigation>
      <Navigation />
    </template>

    <slot />
  </DefaultLayout>
</template>
