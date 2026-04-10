<script setup>
import { inject, ref } from 'vue'
import { throttle } from 'lodash'

const props = defineProps({
  modelValue: {
    type: [String, Number, Object],
    default: null
  },
  users: {
    type: Array,
    default: () => []
  },
  exclude: {
    type: Array,
    default: () => []
  },
  filter: {
    type: Object,
    default: () => ({})
  }
})

defineEmits(['update:modelValue'])

const routePrefix = inject('routePrefix')
const options = ref(props.users)

const onSearch = (search, loading) => {
  if (!search) {
    return
  }

  loading(true)
  fetch(loading, search)
}

const fetch = throttle((loading, search) => {
  axios
    .get(route(`${routePrefix}.users.resources.items`), {
      params: Object.assign(
        {
          keyword: search,
          exclude: props.exclude
        },
        props.filter
      )
    })
    .then(response => {
      loading(false)

      options.value = response.data.data.map(item => {
        return {
          key: item.id,
          label: item.name
        }
      })
    })
}, 350)
</script>
<template>
  <div class="relative">
    <v-select
      :model-value="modelValue"
      :options="options"
      :filterable="false"
      :close-on-select="true"
      :placeholder="$t('user.type_search_user')"
      @update:model-value="$emit('update:modelValue', $event)"
      @search="onSearch"
    >
      <template #no-options>
        {{ $t('general.list_empty') }}
      </template>
    </v-select>
  </div>
</template>
