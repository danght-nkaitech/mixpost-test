<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import useNotifications from '../../../Composables/useNotifications'
import AdminLayout from '@/Layouts/Admin.vue'
import { cloneDeep } from 'lodash'
import Settings from '../../../Layouts/Child/Settings.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Select from '../../../Components/Form/Select.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  configs: {
    required: true,
    type: Object
  },
  stockPhotoProviders: {
    required: true,
    type: Array
  }
})

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const form = useForm(cloneDeep(props.configs))

const { notify } = useNotifications()

const save = () => {
  form.put(route(`${routePrefix}.configs.media.update`), {
    preserveScroll: true,
    onSuccess: () => {
      notify('success', $t('general.saved'))
    }
  })
}
</script>
<template>
  <Head :title="$t('media.media')" />

  <Settings>
    <form @submit.prevent="save">
      <Panel>
        <template #title>{{ $t('media.media') }}</template>

        <HorizontalGroup v-if="props.stockPhotoProviders.length" class="form-field">
          <template #title>
            <label for="stock_photo_provider">Stock Photos Source</label>
          </template>
          <Select id="stock_photo_provider" v-model="form.stock_photo_provider" class="capitalize">
            <option v-for="provider in props.stockPhotoProviders" :key="provider" :value="provider">
              {{ provider }}
            </option>
          </Select>
        </HorizontalGroup>

        <PrimaryButton
          :disabled="form.processing"
          :is-loading="form.processing"
          type="submit"
          class="mt-lg"
        >
          {{ $t('general.save') }}
        </PrimaryButton>
      </Panel>
    </form>
  </Settings>
</template>
