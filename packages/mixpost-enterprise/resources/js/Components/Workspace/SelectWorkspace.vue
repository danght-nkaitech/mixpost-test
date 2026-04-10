<script setup>
import { inject, ref } from 'vue'
import { throttle } from 'lodash'

const props = defineProps({
  modelValue: {
    type: [String, Number, Object],
    default: null
  },
  workspaces: {
    type: Array,
    default: () => []
  },
  exclude: {
    type: Array,
    default: () => []
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue'])

const routePrefix = inject('routePrefix')
const options = ref(props.workspaces)

const onSearch = (search, loading) => {
  if (!search) {
    return
  }

  loading(true)
  fetch(loading, search)
}

const fetch = throttle((loading, search) => {
  axios
    .get(route(`${routePrefix}.workspaces.resources.items`), {
      params: {
        keyword: search,
        exclude: props.exclude
      }
    })
    .then(response => {
      loading(false)

      options.value = response.data.data.map(item => {
        return {
          key: item.uuid,
          label: item.name
        }
      })
    })
}, 350)
</script>
<template>
  <div class="relative w-full">
    <v-select
      :model-value="modelValue"
      :options="options"
      :filterable="false"
      :close-on-select="true"
      :disabled="disabled"
      :placeholder="$t('workspace.search_workspace')"
      @update:model-value="$emit('update:modelValue', $event)"
      @search="onSearch"
    >
      <template #no-options>
        {{ $t('general.list_empty') }}
      </template>
    </v-select>
  </div>
</template>
