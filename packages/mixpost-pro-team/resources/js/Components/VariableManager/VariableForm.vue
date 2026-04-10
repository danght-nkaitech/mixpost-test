<script setup>
import { inject, ref } from 'vue'
import NProgress from 'nprogress'
import Input from '../Form/Input.vue'
import Textarea from '../Form/Textarea.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import Label from '../Form/Label.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'

const workspaceCtx = inject('workspaceCtx')

const props = defineProps({
  variable: {
    type: [Object, null],
    required: false
  }
})

const emit = defineEmits(['store', 'update'])

const isLoading = ref(false)
const form = ref({
  name: props.variable ? props.variable.name : '',
  value: props.variable ? props.variable.value : ''
})

const store = () => {
  NProgress.start()
  isLoading.value = true

  axios
    .post(route('mixpost.variables.store', { workspace: workspaceCtx.id }), form.value)
    .then(response => {
      emit('store', response.data)
    })
    .finally(() => {
      NProgress.done()
      isLoading.value = false
    })
}

const update = () => {
  NProgress.start()
  isLoading.value = true

  axios
    .put(
      route('mixpost.variables.update', {
        workspace: workspaceCtx.id,
        variable: props.variable.id
      }),
      form.value
    )
    .then(() => {
      emit('update', form.value)
      NProgress.done()
      isLoading.value = false
    })
}

const save = () => {
  if (props.variable) {
    update()
    return
  }

  store()
}
</script>
<template>
  <form @submit.prevent="save">
    <VerticalGroup>
      <template #title>
        <Label for="variable_name">{{ $t('general.name') }}</Label>
      </template>
      <Input id="variable_name" v-model="form.name" type="text" required />
    </VerticalGroup>

    <VerticalGroup class="mt-sm">
      <template #title>
        <Label for="variable_value">{{ $t('variable.value') }}</Label>
      </template>
      <Textarea id="variable_value" v-model="form.value" />
    </VerticalGroup>

    <PrimaryButton type="submit" :is-loading="isLoading" class="mt-sm">
      {{ $t('variable.save_variable') }}
    </PrimaryButton>
  </form>
</template>
