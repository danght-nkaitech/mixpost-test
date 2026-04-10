<script setup>
defineProps({
  active: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
  <button
    type="button"
    role="tab"
    :aria-selected="active ? 'true' : 'false'"
    :class="{
      'border-primary-500 text-primary-500': active,
      'hover:text-primary-500 disabled:text-black': !active
    }"
    class="flex shrink-0 items-center border-b-2 border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed pb-xs mr-md rtl:mr-0 rtl:ml-md last:mr-0 last:rtl:ml-0 font-medium transition-colors ease-in-out duration-200"
  >
    <span
      v-if="$slots.icon"
      class="inline-flex"
      :class="{ 'mr-xs rtl:mr-0 rtl:ml-xs': $slots.default }"
    >
      <slot name="icon" />
    </span>

    <slot />
  </button>
</template>
