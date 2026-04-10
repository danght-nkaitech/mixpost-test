<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import WorkspaceLayout from '@/Layouts/Workspace.vue'
import Panel from '@/Components/Surface/Panel.vue'
import DangerButton from '../../../Components/Button/DangerButton.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import Input from '../../../Components/Form/Input.vue'
import ConfirmationModal from '../../../Components/Modal/ConfirmationModal.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import Error from '../../../Components/Form/Error.vue'
import PageHeader from '../../../Components/DataDisplay/PageHeader.vue'

defineOptions({ layout: WorkspaceLayout })

const routePrefix = inject('routePrefix')

const props = defineProps({
  workspace: {
    required: true,
    type: Object
  }
})

const modal = ref(false)
const form = useForm({
  name: ''
})

const openModal = () => {
  modal.value = true
}

const closeModal = () => {
  modal.value = false
  form.reset()
}
const destroy = () => {
  form.delete(
    route(`${routePrefix}.workspace.security.delete`, { workspace: props.workspace.uuid })
  )
}
</script>
<template>
  <Head :title="$t('sidebar.security')" />

  <PageHeader :title="$t('sidebar.security')" :with-padding-x="false" />

  <div class="w-full">
    <Panel>
      <template #title>
        {{ $t('workspace.delete_workspace') }}
      </template>
      <template #description>
        {{ $t('workspace.delete_data') }}
      </template>

      <DangerButton @click="openModal">{{ $t('workspace.delete_workspace') }}</DangerButton>
    </Panel>
  </div>

  <ConfirmationModal :show="modal" variant="danger" @close="closeModal">
    <template #header>
      {{ $t('general.confirmation') }}
    </template>
    <template #body>
      <div class="mb-xs">{{ $t('dashboard.type_name_workspace') }}</div>

      <HorizontalGroup>
        <div>
          <template v-if="modal">
            <Input
              id="name"
              v-model="form.name"
              type="text"
              :placeholder="workspace.name"
              :error="form.errors.name"
              class="w-full"
              autocomplete="off"
              :autofocus="true"
              required
            />
          </template>

          <Error :message="form.errors.name" class="mt-xs" />
        </div>
      </HorizontalGroup>
    </template>
    <template #footer>
      <SecondaryButton class="mr-xs" @click="closeModal">{{
        $t('general.cancel')
      }}</SecondaryButton>
      <DangerButton :disabled="form.processing" :is-loading="form.processing" @click="destroy">
        {{ $t('workspace.delete_workspace') }}
      </DangerButton>
    </template>
  </ConfirmationModal>
</template>
