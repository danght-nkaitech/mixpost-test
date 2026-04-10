<script setup>
import { ref, watch } from 'vue'
import NoResult from '../../../Components/Util/NoResult.vue'
import ListGroup from '../../../Components/DataDisplay/ListGroup.vue'
import ListItem from '../../../Components/DataDisplay/ListItem.vue'
import Avatar from '../../../Components/DataDisplay/Avatar.vue'
import Flex from '../../../Components/Layout/Flex.vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  command: {
    type: Function,
    required: true
  },
  query: {
    type: String,
    required: true
  }
})

const listGroupElm = ref(null)
const selectedIndex = ref(0)

watch(
  () => props.items,
  () => {
    selectedIndex.value = 0
  }
)

const scrollToSelectedItem = () => {
  const listItem = listGroupElm.value?.$el.querySelectorAll('.list-item')[selectedIndex.value]

  if (listItem) {
    listItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' })
  }
}

// Handlers
const upHandler = () => {
  selectedIndex.value = (selectedIndex.value + props.items.length - 1) % props.items.length
  scrollToSelectedItem()
}

const downHandler = () => {
  selectedIndex.value = (selectedIndex.value + 1) % props.items.length
  scrollToSelectedItem()
}

const enterHandler = () => {
  selectItem(selectedIndex.value)
}

const selectItem = index => {
  const item = props.items[index]

  if (item) {
    props.command({ id: item.id, label: item.name })
  }
}

const onKeyDown = ({ event }) => {
  if (event.key === 'ArrowUp') {
    upHandler()
    return true
  }

  if (event.key === 'ArrowDown') {
    downHandler()
    return true
  }

  if (event.key === 'Enter') {
    enterHandler()
    return true
  }

  return false
}

defineExpose({
  onKeyDown
})
</script>
<template>
  <div class="bg-white rounded-md shadow-mix relative">
    <template v-if="items.length">
      <ListGroup ref="listGroupElm" class="overflow-y-auto mixpost-scroll-style max-h-72">
        <template v-for="(item, index) in items" :key="index">
          <ListItem
            :active="index === selectedIndex"
            tabindex="0"
            class="list-item"
            role="button"
            @click="selectItem(index)"
          >
            <Flex :responsive="false">
              <Avatar :name="item.name" size="sm" class="cursor-default" />
              <div>{{ item.name }}</div>
            </Flex>
          </ListItem>
        </template>
      </ListGroup>
    </template>
    <template v-else>
      <NoResult :with-padding="true" />
    </template>
  </div>
</template>
