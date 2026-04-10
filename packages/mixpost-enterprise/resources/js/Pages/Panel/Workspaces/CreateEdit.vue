<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import { cloneDeep, random } from 'lodash'
import { ACCESS_STATUS_SUBSCRIPTION } from '@/Constants/Workspace.js'
import usePageMode from '@/Composables/usePageMode'
import useNotifications from '@/Composables/useNotifications'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Error from '@/Components/Form/Error.vue'
import Input from '@/Components/Form/Input.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import DialogModal from '../../../Components/Modal/DialogModal.vue'
import ColorPicker from '@mJs/Components/Package/ColorPicker.vue'
import { COLOR_PALLET_LIST } from '@mJs/Constants/ColorPallet'
import SelectUser from '../../../Components/User/SelectUser/SelectUser.vue'
import LabelSuffix from '../../../Components/Form/LabelSuffix.vue'
import Actions from '../../../Components/Workspace/Actions.vue'
import Select from '../../../Components/Form/Select.vue'
import Alert from '../../../Components/Util/Alert.vue'

const props = defineProps({
  mode: {
    type: String,
    default: 'create'
  },
  workspace: {
    type: Object
  }
})

const { t: $t } = useI18n()

const { notify } = useNotifications()

const routePrefix = inject('routePrefix')

const pageTitle = computed(() => {
  if (isCreate.value) {
    return $t('dashboard.create_workspace')
  }

  return $t('workspace.edit_workspace')
})

const { isCreate, isEdit } = usePageMode()

const changeColorModal = ref(false)
const changeColorHex = ref('')

const selectedOwner = ref(
  isEdit.value && props.workspace.owner
    ? {
        key: props.workspace.owner.id,
        label: props.workspace.owner.name,
        email: props.workspace.owner.email
      }
    : null
)

const form = useForm(
  isEdit.value
    ? cloneDeep(props.workspace)
    : {
        name: '',
        hex_color: '',
        access_status: ACCESS_STATUS_SUBSCRIPTION
      }
)

const selectColor = () => {
  form.hex_color = changeColorHex.value
  changeColorModal.value = false
}

const pickRandomColor = () => {
  const colorList = COLOR_PALLET_LIST()

  return colorList[random(0, colorList.length - 1)]
}

onMounted(() => {
  if (isCreate.value) {
    const randomColor = pickRandomColor()

    form.hex_color = randomColor
    changeColorHex.value = randomColor
  }

  if (isEdit.value) {
    changeColorHex.value = props.workspace.hex_color
  }
})

const getTransformedForm = () => {
  return form.transform(data => ({
    ...data,
    ...{
      owner_id: selectedOwner.value ? selectedOwner.value.key : null
    }
  }))
}

const store = () => {
  getTransformedForm().post(route(`${routePrefix}.workspaces.store`))
}

const update = () => {
  getTransformedForm().put(
    route(`${routePrefix}.workspaces.update`, { workspace: props.workspace.uuid }),
    {
      onSuccess: () => {
        notify('success', $t('workspace.workspace_updated'))
      }
    }
  )
}

const submit = () => {
  if (isCreate.value) {
    store()
  }

  if (isEdit.value) {
    update()
  }
}
</script>
<template>
  <Head :title="pageTitle" />

  <div class="row-py w-full mx-auto">
    <PageHeader :title="pageTitle">
      <template v-if="isEdit">
        <Actions :workspace="workspace" :edit="false" />
      </template>
    </PageHeader>

    <div class="row-px">
      <form method="post" @submit.prevent="submit">
        <Panel>
          <template #title>{{ $t('general.details') }}</template>

          <HorizontalGroup>
            <template #title>
              <label for="name"
                >{{ $t('general.name') }}
                <LabelSuffix danger>*</LabelSuffix>
              </label>
            </template>

            <Input
              id="name"
              v-model="form.name"
              type="text"
              :placeholder="$t('workspace.workspace_name')"
              class="w-full"
              autocomplete="off"
              :autofocus="isCreate"
              required
            />

            <template #footer>
              <Error :message="form.errors.name" />
            </template>
          </HorizontalGroup>

          <HorizontalGroup class="mt-lg">
            <template #title>
              <label for="access_status">{{ $t('general.access_status') }}</label>
            </template>
            <Select id="access_status" v-model="form.access_status">
              <option value="subscription">{{ $t('subscription.requires_subscription') }}</option>
              <option value="unlimited">{{ $t('workspace.unlimited') }}</option>
              <option value="locked">{{ $t('workspace.locked') }}</option>
            </Select>
            <template #footer>
              <Error :message="form.errors.access_status" />
            </template>
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
        </Panel>

        <Panel class="mt-lg">
          <template #title>{{ $t('general.owner') }}</template>

          <div class="form-field">
            <SelectUser v-model="selectedOwner" :users="selectedOwner ? [selectedOwner] : []" />

            <Alert v-if="isEdit" :closeable="false" class="mt-lg"
              >{{ $t('panel.add_admin_role') }}
            </Alert>

            <Error :message="form.errors.owner_id" />
          </div>
        </Panel>

        <PrimaryButton
          type="submit"
          class="mt-lg"
          :disabled="form.processing"
          :is-loading="form.processing"
        >
          {{ isCreate ? $t('general.create') : $t('workspace.update') }}
        </PrimaryButton>
      </form>
    </div>
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
      <SecondaryButton class="mr-xs rtl:mr-0 rtl:ml-xs" @click="changeColorModal = false">
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <PrimaryButton @click="selectColor">{{ $t('general.done') }}</PrimaryButton>
    </template>
  </DialogModal>
</template>
