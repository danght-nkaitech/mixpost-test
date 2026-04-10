<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import useNotifications from '../../../Composables/useNotifications'
import WorkspaceLayout from '@/Layouts/Workspace.vue'
import Panel from '@/Components/Surface/Panel.vue'
import ColorPicker from '@mJs/Components/Package/ColorPicker.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import Error from '../../../Components/Form/Error.vue'
import DialogModal from '../../../Components/Modal/DialogModal.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import Input from '../../../Components/Form/Input.vue'
import PageHeader from '../../../Components/DataDisplay/PageHeader.vue'
import ClipboardCard from '../../../Components/Util/ClipboardCard.vue'

defineOptions({ layout: WorkspaceLayout })

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const { notify } = useNotifications()

const props = defineProps({
  workspace: {
    required: true,
    type: Object
  }
})

const form = useForm({
  name: props.workspace.name,
  hex_color: props.workspace.hex_color
})

const changeColorModal = ref(false)
const changeColorHex = ref(props.workspace.hex_color)

const selectColor = () => {
  form.hex_color = changeColorHex.value
  changeColorModal.value = false
}

const save = () => {
  form.put(route(`${routePrefix}.workspace.settings.update`, { workspace: props.workspace.uuid }), {
    preserveScroll: true,
    onSuccess: () => {
      notify('success', $t('dashboard.settings_saved'))
    }
  })
}
</script>
<template>
  <Head :title="$t('general.settings')" />

  <PageHeader :title="$t('general.settings')" :with-padding-x="false" />

  <div class="w-ful">
    <form method="post" @submit.prevent="save">
      <Panel>
        <template #title>{{ $t('general.details') }}</template>
        <template #description>{{ $t('general.basic_info_desc') }}</template>

        <div>
          <HorizontalGroup>
            <template #title>
              <label for="name">{{ $t('general.name') }}</label>
            </template>

            <div class="w-full">
              <Input
                id="name"
                v-model="form.name"
                type="text"
                :placeholder="$t('workspace.workspace_name')"
                class="w-full"
                autocomplete="off"
                required
              />
              <Error :message="form.errors.name" class="mt-1" />
            </div>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              {{ $t('theme.color') }}
            </template>

            <div
              :style="{ background: form.hex_color }"
              role="button"
              type="button"
              class="w-xl h-xl rounded-md"
              @click="changeColorModal = true"
            />
          </HorizontalGroup>

          <div class="flex items-center mt-lg">
            <PrimaryButton type="submit" :disabled="form.processing"
              >{{ $t('general.save') }}
            </PrimaryButton>
          </div>
        </div>
      </Panel>
    </form>

    <template v-if="$page.props.mixpost.features.api_access_tokens">
      <Panel class="mt-lg">
        <template #title>
          {{ $t('system.usage_api') }}
        </template>

        <ClipboardCard>{{ workspace.uuid }}</ClipboardCard>
      </Panel>
    </template>
  </div>

  <DialogModal :show="changeColorModal" max-width="md" @close="changeColorModal = false">
    <template #header>
      {{ $t('workspace.change_workspace_color') }}
    </template>
    <template #body>
      <template v-if="changeColorModal">
        <ColorPicker v-model="changeColorHex" />
      </template>
    </template>
    <template #footer>
      <SecondaryButton class="mr-xs" @click="changeColorModal = false">
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <PrimaryButton @click="selectColor">{{ $t('general.done') }}</PrimaryButton>
    </template>
  </DialogModal>
</template>
