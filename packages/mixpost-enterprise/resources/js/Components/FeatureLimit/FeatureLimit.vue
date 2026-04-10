<script setup>
import { computed } from 'vue'
import { find } from 'lodash'
import CountNumber from './FormFields/CountNumber.vue'
import Trash from '../../Icons/Trash.vue'
import PureButton from '../Button/PureButton.vue'

const components = {
  CountNumber
}

defineEmits(['remove'])

const props = defineProps({
  resource: {
    type: Object,
    required: true
  },
  form: {
    type: Object,
    required: true
  }
})

const fields = computed(() => {
  return props.resource.form.map(item => {
    const model = find(props.form, { name: item.name })

    return {
      item,
      model: model ? model : item.form
    }
  })
})
</script>

<template>
  <div class="border border-gray-200 rounded-lg w-full group">
    <div class="border-b p-md">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">{{ resource.name }}</div>
          <div v-if="resource.description" class="flex">
            {{ resource.description }}
          </div>
        </div>

        <div v-if="!$attrs.readOnly" class="opacity-50 group-hover:opacity-100 transition-opacity">
          <PureButton @click="$emit('remove', resource.code)">
            <Trash class="text-red-500" />
          </PureButton>
        </div>
      </div>
    </div>

    <div class="max-w-(--container-xl) w-full p-md">
      <template v-for="field in fields" :key="field.item.name">
        <component
          :is="components[field.item.component]"
          v-model="field.model.value"
          :read-only="$attrs.readOnly"
        />
      </template>
    </div>
  </div>
</template>
