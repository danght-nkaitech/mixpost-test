<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { inject, onMounted, ref } from 'vue'
import { random } from 'lodash'
import MinimalLayout from '@/Layouts/Minimal.vue'
import Panel from '@/Components/Surface/Panel.vue'
import ColorPicker from '@mJs/Components/Package/ColorPicker.vue'
import { COLOR_PALLET_LIST } from '@mJs/Constants/ColorPallet'
import DialogModal from '../../../Components/Modal/DialogModal.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import Error from '../../../Components/Form/Error.vue'
import Input from '../../../Components/Form/Input.vue'

defineOptions({ layout: MinimalLayout })

const routePrefix = inject('routePrefix')

const form = useForm({
  name: '',
  hex_color: ''
})

const changeColorModal = ref(false)
const changeColorHex = ref('')

const selectColor = () => {
  form.hex_color = changeColorHex.value
  changeColorModal.value = false
}

const pickRandomColor = () => {
  const colorList = COLOR_PALLET_LIST()

  return colorList[random(0, colorList.length - 1)]
}

onMounted(() => {
  const randomColor = pickRandomColor()

  form.hex_color = randomColor
  changeColorHex.value = randomColor
})

const submit = () => {
  form
    .transform(data => ({
      ...data,
      login: true
    }))
    .post(route(`${routePrefix}.workspace.store`))
}
</script>
<template>
  <Head :title="$t('dashboard.create_workspace')" />

  <div class="w-full mx-auto">
    <form method="post" @submit.prevent="submit">
      <Panel>
        <template #title>
          {{ $t('dashboard.create_workspace') }}
        </template>

        <div class="sm:max-w-(--container-lg)">
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
                :autofocus="true"
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
            <PrimaryButton
              type="submit"
              :is-loading="form.processing"
              :disabled="form.processing"
              >{{ $t('general.create') }}</PrimaryButton
            >
          </div>
        </div>
      </Panel>
    </form>
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
      <SecondaryButton class="mr-xs" @click="changeColorModal = false"
        >{{ $t('general.cancel') }}
      </SecondaryButton>
      <PrimaryButton @click="selectColor">{{ $t('general.done') }}</PrimaryButton>
    </template>
  </DialogModal>
</template>
