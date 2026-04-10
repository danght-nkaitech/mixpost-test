<script setup>
import { computed, inject } from 'vue'
import ViewDefault from './ViewDefault.vue'
import ViewThread from './ViewThread.vue'

defineProps({
  post: {
    required: true,
    type: Object
  }
})

const postCtx = inject('postCtx')

const isThread = computed(() => {
  return postCtx.activity.thread.parent_comment !== null
})
</script>
<template>
  <div class="flex flex-col h-full">
    <!--        <div class="flex items-center space-x-md row-px">-->
    <!--            <button class="text-blue-600 relative">-->
    <!--                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"-->
    <!--                     stroke="currentColor">-->
    <!--                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
    <!--                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>-->
    <!--                </svg>-->
    <!--                <span-->
    <!--                    class="absolute -top-1 -right-1 bg-blue-600 text-white rounded-full text-xs w-4 h-4 flex items-center justify-center">2</span>-->
    <!--            </button>-->
    <!--        </div>-->

    <template v-if="!isThread">
      <ViewDefault :post="post" />
    </template>

    <template v-if="isThread">
      <ViewThread :post="post" />
    </template>
  </div>
</template>
