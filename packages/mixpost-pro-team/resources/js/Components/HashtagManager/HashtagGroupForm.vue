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
  group: {
    type: [Object, null],
    required: false
  }
})

const emit = defineEmits(['store', 'update'])

const isLoading = ref(false)
const form = ref({
  name: props.group ? props.group.name : '',
  content: props.group ? props.group.content : ''
})

const store = () => {
  NProgress.start()
  isLoading.value = true

  axios
    .post(route('mixpost.hashtaggroups.store', { workspace: workspaceCtx.id }), form.value)
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
      route('mixpost.hashtaggroups.update', {
        workspace: workspaceCtx.id,
        hashtaggroup: props.group.id
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
  if (props.group) {
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
        <Label for="group_name">{{ $t('hashtag.hashtag_name') }}</Label>
      </template>
      <Input id="group_name" v-model="form.name" type="text" required />
    </VerticalGroup>

    <VerticalGroup class="mt-sm">
      <template #title>
        <Label for="group_content">{{ $t('hashtag.hashtag_content') }}</Label>
      </template>
      <Textarea id="group_content" v-model="form.content" rows="3" class="resize-none" required />
    </VerticalGroup>

    <PrimaryButton type="submit" :is-loading="isLoading" class="mt-sm">{{
      $t('hashtag.save_hashtag_group')
    }}</PrimaryButton>
  </form>
</template>
