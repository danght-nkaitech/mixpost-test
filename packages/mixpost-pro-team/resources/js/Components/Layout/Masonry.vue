<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  columns: {
    type: Number,
    default: 3
  },
  columnWidth: {
    type: Number,
    default: 220
  },
  adaptive: {
    type: Boolean,
    default: true
  }
})

const containerRef = ref(null)
const containerWidth = ref(0)

const updateWidth = () => {
  if (containerRef.value) {
    containerWidth.value = containerRef.value.offsetWidth
  }
}

onMounted(() => {
  updateWidth()
  window.addEventListener('resize', updateWidth)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateWidth)
})

const columnCount = computed(() => {
  if (!props.adaptive) return props.columns

  if (!containerWidth.value) return props.columns

  return Math.max(1, Math.floor(containerWidth.value / props.columnWidth))
})

const columnsList = computed(() => {
  const cols = Array.from({ length: columnCount.value }, () => [])

  props.items.forEach((item, index) => {
    cols[index % columnCount.value].push({ item, index })
  })

  return cols
})
</script>

<template>
  <div ref="containerRef" class="flex gap-xs">
    <div
      v-for="(column, colIndex) in columnsList"
      :key="colIndex"
      class="flex flex-col gap-xs grow basis-0"
    >
      <div v-for="{ item, index } in column" :key="index">
        <slot :item="item" :index="index">{{ item }}</slot>
      </div>
    </div>
  </div>
</template>
