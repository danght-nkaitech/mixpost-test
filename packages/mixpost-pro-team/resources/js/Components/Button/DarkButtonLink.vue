<script setup>
import { Link } from '@inertiajs/vue3'
import useButtonSize from '@/Composables/useButtonSize'

const props = defineProps({
  href: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'lg'
  },
  hiddenTextOnSmallScreen: {
    type: Boolean,
    default: false
  }
})

const { sizeClass } = useButtonSize(props.size)
</script>

<template>
  <Link
    :href="href"
    :class="sizeClass"
    class="inline-flex items-center bg-primary-800 border border-transparent rounded-md font-medium text-xs text-primary-context uppercase tracking-widest rtl:tracking-normal hover:bg-primary-900 active:bg-primary-900 focus:border-primary-900 focus:shadow-outline-indigo transition ease-in-out duration-200"
  >
    <span
      v-if="$slots.icon"
      class="inline-flex"
      :class="{
        'sm:mr-xs sm:rtl:mr-0 sm:rtl:ml-xs': $slots.default,
        'mr-0 sm:mr-xs sm:rtl:mr-0 sm:rtl:ml-xs': hiddenTextOnSmallScreen,
        'mr-xs rtl:mr-xs rtl:ml-xs': !hiddenTextOnSmallScreen && $slots.default
      }"
    >
      <slot name="icon" />
    </span>

    <span
      v-if="$slots.default"
      class="inline-flex items-center"
      :class="{ 'hidden sm:inline': hiddenTextOnSmallScreen }"
    >
      <slot />
    </span>
  </Link>
</template>
