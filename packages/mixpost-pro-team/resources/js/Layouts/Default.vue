<script setup>
import { onMounted, onUnmounted, provide, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import useBootstrap from '../Composables/useBootstrap'
import Notifications from '@/Components/Util/Notifications.vue'
import Confirmation from '../Plugins/Confirmation/Confirmation.vue'
import Preloader from '../Components/Util/Preloader.vue'
import useBroadcast from '../Composables/useBroadcast'

const context = reactive({
  showAside: false,
  preloader: false
})

provide('appCtx', context)

const removeStartEventListener = router.on('start', () => {
  context.showAside = false
})

const { bootstrapComplete } = useBootstrap()
const { openBroadcast, closeBroadcast } = useBroadcast()

onMounted(() => {
  openBroadcast()
})

onUnmounted(() => {
  removeStartEventListener()
  closeBroadcast()
})
</script>
<template>
  <div class="flex flex-row h-screen min-h-full bg-stone-500">
    <template v-if="!bootstrapComplete">
      <Preloader />
    </template>

    <template v-else>
      <template v-if="context.preloader">
        <Preloader :opacity="75" class="!z-20" />
      </template>

      <aside
        :class="{
          'translate-x-0': context.showAside,
          '-translate-x-full xl:translate-x-0 rtl:translate-x-full xl:rtl:translate-x-0':
            !context.showAside
        }"
        class="default-sidebar aside fixed xl:relative h-full z-20 xl:z-10 transition-transform ease-in-out duration-200"
      >
        <slot name="sidebar" />
      </aside>

      <main class="w-full xl:main flex flex-col overflow-y-auto" scroll-region>
        <slot name="navigation" />
        <slot />
      </main>
    </template>

    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-show="context.showAside"
        class="fixed inset-0 z-10 transform transition-all"
        @click="context.showAside = false"
      >
        >
        <div class="absolute inset-0 bg-primary-900 opacity-60" />
      </div>
    </transition>

    <Notifications />

    <Confirmation />

    <AuthPasswordConfirmation />
  </div>
</template>
