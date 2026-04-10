<script setup>
import { computed, ref } from 'vue'
import { decomposeString } from '@/helpers'
import Plus from '../../Icons/Plus.vue'
import DialogModal from '../Modal/DialogModal.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import ListGroup from '../DataDisplay/ListGroup.vue'
import ListItem from '../DataDisplay/ListItem.vue'
import Input from '../Form/Input.vue'
import NoResult from '../Util/NoResult.vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  addedResources: {
    type: Array,
    default: () => []
  }
})

defineEmits(['select'])

const modal = ref(false)
const searchText = ref('')

const renderItems = computed(() => {
  return props.items.filter(limitItem => {
    const search = decomposeString(limitItem.name)
      .toLocaleLowerCase()
      .includes(searchText.value.toLocaleLowerCase())

    return !props.addedResources.includes(limitItem.code) && search
  })
})

const open = () => {
  modal.value = true
}

const close = () => {
  modal.value = false
}
</script>

<template>
  <SecondaryButton size="sm" @click="open">
    <template #icon>
      <Plus />
    </template>
    {{ $t('plan.add_limit') }}
  </SecondaryButton>

  <DialogModal
    :show="modal"
    max-width="lg"
    :scrollable-body="true"
    :closeable="true"
    @close="close"
  >
    <template #header>
      {{ $t('plan.feature_limit') }}
    </template>

    <template #body>
      <div class="mt-xs">
        <Input
          v-model="searchText"
          type="text"
          autofocus
          :placeholder="$t('plan.search_plan')"
          class="w-full"
        />
      </div>

      <ListGroup v-if="renderItems.length" class="mt-xs">
        <template v-for="item in renderItems" :key="item.code">
          <ListItem
            tabindex="0"
            role="button"
            @click="
              $emit('select', {
                code: item.code,
                form: item.form.map(field => {
                  return { name: field.name, value: field.value }
                })
              })
            "
          >
            <div>{{ item.name }}</div>
            <div class="text-sm">{{ item.description }}</div>
          </ListItem>
        </template>
      </ListGroup>

      <NoResult v-if="!renderItems.length" class="mt-md">
        <span v-if="searchText">{{ $t('plan.no_feature_limit') }}</span>
        <span v-else>{{ $t('plan.limits_reached') }}</span>
      </NoResult>
    </template>

    <template #footer>
      <SecondaryButton @click="close">{{ $t('general.done') }}</SecondaryButton>
    </template>
  </DialogModal>
</template>
