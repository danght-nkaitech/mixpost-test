<script setup>
import { inject } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { cloneDeep } from 'lodash'
import useNotifications from '../../../Composables/useNotifications'
import AdminLayout from '@/Layouts/Admin.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import Settings from '../../../Layouts/Child/Settings.vue'
import Select from '@/Components/Form/Select.vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: AdminLayout })

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const props = defineProps({
  configs: {
    required: true,
    type: Object
  },
  urlShortenerProviders: {
    required: true,
    type: Array
  }
})

const form = useForm(cloneDeep(props.configs))

const { notify } = useNotifications()

const save = () => {
  form.put(route(`${routePrefix}.configs.general.update`), {
    preserveScroll: true,
    onSuccess: () => {
      notify('success', $t('general.saved'))
    }
  })
}
</script>
<template>
  <Head :title="$t('general.general')" />

  <Settings>
    <form method="post" @submit.prevent="save">
      <Panel>
        <template #title>{{ $t('general.general') }}</template>

        <HorizontalGroup v-if="props.urlShortenerProviders.length" class="form-field mt-lg">
          <template #title>
            <label for="url_shortener_provider">URL Shortener Source</label>
          </template>
          <Select
            id="url_shortener_provider"
            v-model="form.url_shortener_provider"
            class="capitalize"
          >
            <option value="disabled">{{ $t('general.disabled') }}</option>
            <option v-for="source in props.urlShortenerProviders" :key="source" :value="source">
              {{ source }}
            </option>
          </Select>
        </HorizontalGroup>

        <div class="flex items-center mt-lg">
          <PrimaryButton type="submit" :disabled="form.processing" :is-loading="form.processing"
            >{{ $t('general.save') }}
          </PrimaryButton>
        </div>
      </Panel>
    </form>
  </Settings>
</template>
