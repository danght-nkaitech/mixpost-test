<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { decomposeString } from '../../../helpers'
import ListGroup from '../../DataDisplay/ListGroup.vue'
import ListItem from '../../DataDisplay/ListItem.vue'
import Input from '../../Form/Input.vue'
import Sparkles from '../../../Icons/Sparkles.vue'
import PlusSimple from '../../../Icons/PlusSimple.vue'
import Minus from '../../../Icons/Minus.vue'
import Flex from '../../Layout/Flex.vue'
import NoResult from '../../Util/NoResult.vue'

const { t: $t } = useI18n()

const commands = [
  {
    name: 'rephrase',
    nameLocalized: $t('ai.rephrase'),
    icon: 'Sparkles'
  },
  {
    name: 'fix_spelling_grammar',
    nameLocalized: $t('ai.fix_spelling_grammar'),
    icon: 'Sparkles'
  },
  {
    name: 'expand',
    nameLocalized: $t('ai.expand'),
    icon: 'PlusSimple'
  },
  {
    name: 'shorten',
    nameLocalized: $t('ai.shorten'),
    icon: 'Minus'
  },
  {
    name: 'simplify',
    nameLocalized: $t('ai.simplify'),
    icon: 'Sparkles'
  },
  {
    name: 'friendly_tone',
    nameLocalized: $t('ai.friendly_tone'),
    icon: '',
    emoji: '😊'
  },
  {
    name: 'formal_tone',
    nameLocalized: $t('ai.formal_tone'),
    icon: '',
    emoji: '🎩'
  },
  {
    name: 'edgy_tone',
    nameLocalized: $t('ai.edgy_tone'),
    icon: '',
    emoji: '🤘'
  },
  {
    name: 'engaging_tone',
    nameLocalized: $t('ai.engaging_tone'),
    icon: '',
    emoji: '🤝'
  },
  {
    name: 'generate',
    nameLocalized: $t('general.generate_new'),
    icon: 'Sparkles'
  }
]

const emit = defineEmits(['selected-command'])

const searchText = ref('')
const selected = ref('improve')
const highlightedIndex = ref(0)

const iconComponents = {
  Sparkles,
  PlusSimple,
  Minus
}

const availableCommands = computed(() => {
  return commands.filter(command => {
    return decomposeString(command.name)
      .toLocaleLowerCase()
      .includes(searchText.value.toLocaleLowerCase())
  })
})

const select = command => {
  selected.value = command.name

  emit('selected-command', selected.value)
}

const navigateCommands = event => {
  if (event.key === 'ArrowDown' || event.key === 'Tab') {
    if (highlightedIndex.value < availableCommands.value.length - 1) {
      highlightedIndex.value++
    } else {
      highlightedIndex.value = 0 // Wrap around to the start of the array
    }
  } else if (event.key === 'ArrowUp') {
    if (highlightedIndex.value > 0) {
      highlightedIndex.value--
    } else {
      highlightedIndex.value = availableCommands.value.length - 1 // Wrap around to the end of the array
    }
  }

  selected.value = availableCommands.value[highlightedIndex.value].name
}

watch(searchText, () => {
  if (availableCommands.value.length > 0) {
    selected.value = availableCommands.value[0].name
  }
})
</script>
<template>
  <div
    @keydown.up.prevent="navigateCommands"
    @keydown.down.prevent="navigateCommands"
    @keydown.tab.prevent="navigateCommands"
  >
    <div class="mt-xs">
      <Input
        v-model="searchText"
        type="text"
        autofocus
        :placeholder="$t('general.search_jump')"
        class="w-full"
        @keydown.enter="$emit('selected-command', selected)"
      />
    </div>

    <ListGroup class="mt-xs">
      <template v-for="command in availableCommands" :key="command.name">
        <ListItem
          :active="selected === command.name"
          tabindex="0"
          role="button"
          @click="select(command)"
        >
          <Flex>
            <template v-if="command.icon">
              <component :is="iconComponents[command.icon]" class="text-stone-800" />
            </template>
            <template v-else>
              <div class="mr-[8px]">{{ command.emoji }}</div>
            </template>
            {{ command.nameLocalized }}
          </Flex>
        </ListItem>
      </template>

      <template v-if="!availableCommands.length">
        <NoResult class="p-md" />
      </template>
    </ListGroup>
  </div>
</template>
