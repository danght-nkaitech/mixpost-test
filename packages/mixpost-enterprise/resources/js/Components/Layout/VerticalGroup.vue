<script setup>
defineProps({
  forceFullWidth: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
  <div>
    <div
      :class="{ 'w-full': forceFullWidth, 'form-field': !forceFullWidth }"
      class="flex justify-between items-center"
    >
      <div class="w-full flex flex-col sm:items-center sm:justify-between">
        <div v-if="$slots.title || $slots.description" class="flex flex-col justify-start w-full">
          <div v-if="$slots.title" class="font-medium">
            <slot name="title" />
          </div>

          <div v-if="$slots.description" class="text-gray-500">
            <slot name="description" />
          </div>
        </div>

        <div v-if="$slots.default" class="w-full flex justify-start mt-xs">
          <slot />
        </div>
      </div>
    </div>
    <div v-if="$slots.footer" class="mt-xs">
      <slot name="footer" />
    </div>
  </div>
</template>
