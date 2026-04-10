<script setup>
import { computed, inject, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { difference, first, random } from 'lodash'
import usePost from '@/Composables/usePost'
import { decomposeString } from '@/helpers'
import { COLOR_PALLET_LIST } from '@/Constants/ColorPallet'
import Tag from '@/Components/DataDisplay/Tag.vue'
import TagIcon from '@/Icons/Tag.vue'
import DialogModal from '@/Components/Modal/DialogModal.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import Input from '@/Components/Form/Input.vue'
import Preloader from '@/Components/Util/Preloader.vue'
import ListGroup from '../DataDisplay/ListGroup.vue'
import ListItem from '../DataDisplay/ListItem.vue'

const workspaceCtx = inject('workspaceCtx')

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update'])

const { editAllowed } = usePost()

const showManager = ref(false)
const searchText = ref('')
const isLoading = ref(false)

const tags = computed(() => {
  return usePage().props.tags
})

const availableTags = computed(() => {
  return tags.value.filter(tag => {
    const search = decomposeString(tag.name)
      .toLocaleLowerCase()
      .includes(searchText.value.toLocaleLowerCase())

    return !props.items.some(item => item.id === tag.id) && search
  })
})

const openManager = () => {
  showManager.value = true
}

const closeManager = () => {
  showManager.value = false
  searchText.value = ''
}

const select = (tag, $event = null) => {
  if (!$event || ($event && !$event.target.closest('.tag-actions'))) {
    emit('update', [...props.items.slice(0), ...[tag]])
  }
}

const remove = tag => {
  emit(
    'update',
    props.items.filter(item => item.id !== tag.id)
  )
}

const store = () => {
  if (isLoading.value) {
    return
  }

  if (!searchText.value) {
    return
  }

  // Avoid duplicate tags with the same name
  if (
    availableTags.value.length === 1 &&
    searchText.value &&
    decomposeString(searchText.value) === decomposeString(availableTags.value[0].name)
  ) {
    searchText.value = ''
    select(availableTags.value[0])
    return
  }

  const pickRandomColor = () => {
    const colorList = COLOR_PALLET_LIST()

    const nonUsedColors = difference(
      colorList,
      tags.value.map(tag => tag.hex_color)
    )

    if (!nonUsedColors.length) {
      return colorList[random(0, colorList.length - 1)]
    }

    return nonUsedColors[random(0, nonUsedColors.length - 1)]
  }

  router.post(
    route('mixpost.tags.store', { workspace: workspaceCtx.id }),
    {
      name: searchText.value,
      hex_color: pickRandomColor()
    },
    {
      onStart() {
        isLoading.value = true
      },
      onSuccess() {
        searchText.value = ''
        select(first(tags.value))
      },
      onFinish() {
        isLoading.value = false
      }
    }
  )
}
</script>
<template>
  <div>
    <div class="flex items-center rtl:ml-xs">
      <div
        class="hidden lg:flex items-center space-x-xs rtl:space-x-reverse mr-xs rtl:mr-0 rtl:ml-xs"
      >
        <template v-for="item in items" :key="item.id">
          <Tag :item="item" :removable="editAllowed" @remove="remove(item)" />
        </template>
      </div>

      <SecondaryButton
        v-if="editAllowed"
        :hidden-text-on-small-screen="true"
        size="md"
        @click="openManager"
      >
        <template #icon>
          <TagIcon />
        </template>
        {{ $t('post.labels') }}
      </SecondaryButton>
    </div>

    <DialogModal
      :show="showManager"
      max-width="sm"
      :closeable="true"
      :scrollable-body="true"
      @close="closeManager"
    >
      <template #header>
        {{ $t('post.labels') }}
      </template>

      <template #body>
        <div class="relative">
          <Preloader v-if="isLoading" :opacity="50" />

          <div class="flex flex-wrap items-center gap-xs">
            <div v-for="item in items" :key="item.id">
              <Tag :item="item" @remove="remove(item)" />
            </div>
          </div>

          <div class="mt-xs">
            <Input
              v-model="searchText"
              type="text"
              autofocus
              :placeholder="$t('tag.search_create_post')"
              class="w-full"
              @keyup.enter="store"
            />
          </div>

          <ListGroup v-if="availableTags.length" class="mt-xs">
            <template v-for="item in availableTags" :key="item.id">
              <ListItem tabindex="0" role="button" @click="select(item, $event)">
                <div class="flex">
                  <Tag :item="item" :removable="false" />
                </div>
              </ListItem>
            </template>
          </ListGroup>

          <div v-if="searchText" class="mt-4 text-stone-800 italic">
            {{ $t('tag.create_new_label') }}
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton class="mr-xs" @click="closeManager">
          {{ $t('general.done') }}</SecondaryButton
        >
      </template>
    </DialogModal>
  </div>
</template>
