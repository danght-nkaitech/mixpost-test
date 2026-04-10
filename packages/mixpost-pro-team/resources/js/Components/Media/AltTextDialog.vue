<script setup>
import { inject, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { clone } from 'lodash'
import DialogModal from '@/Components/Modal/DialogModal.vue'
import Textarea from '@/Components/Form/Textarea.vue'
import VerticalGroup from '@/Components/Layout/VerticalGroup.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'

const routePrefix = inject('routePrefix')
const workspaceCtx = inject('workspaceCtx')

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  media: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'updated'])

const form = useForm({
  alt_text: ''
})

watch(
  () => props.show,
  val => {
    if (val && props.media) {
      form.alt_text = props.media.alt_text || ''
    }
  }
)

const submit = () => {
  form.put(
    route(`${routePrefix}.media.update`, {
      item: props.media.uuid,
      workspace: workspaceCtx.id
    }),
    {
      onSuccess: () => {
        const updated = clone(props.media)
        updated.alt_text = form.alt_text
        emit('updated', updated)
        close()
      }
    }
  )
}

const close = () => {
  form.reset()
  emit('close')
}
</script>
<template>
  <DialogModal :show="show" max-width="md" :closeable="true" :scrollable-body="true" @close="close">
    <template #header>
      {{ $t('media.edit_alt_text') }}
    </template>

    <template #body>
      <div>
        {{
          $t('media.add_alt_text_for', {
            platforms: 'Facebook, Instagram, Threads, X, Mastodon, Pinterest, Bluesky and LinkedIn'
          })
        }}
      </div>
      <VerticalGroup :force-full-width="true" class="mt-lg">
        <template #title>{{ $t('media.edit_alt_text') }}</template>
        <Textarea v-model="form.alt_text" :placeholder="$t('media.write_alt_text')" />
      </VerticalGroup>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="close">{{ $t('general.cancel') }}</SecondaryButton>
      <PrimaryButton :is-loading="form.processing" :disabled="form.processing" @click="submit">
        {{ $t('general.save') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
