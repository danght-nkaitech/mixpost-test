<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, Link, useForm } from '@inertiajs/vue3'
import useNotifications from '../../../Composables/useNotifications'
import AdminLayout from '@/Layouts/Admin.vue'
import { cloneDeep } from 'lodash'
import Settings from '../../../Layouts/Child/Settings.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Select from '../../../Components/Form/Select.vue'
import Alert from '../../../Components/Util/Alert.vue'
import Textarea from '../../../Components/Form/Textarea.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  configs: {
    required: true,
    type: Object
  },
  providers: {
    required: true,
    type: Object
  },
  isConfigured: {
    required: true,
    type: Boolean
  }
})

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const form = useForm(cloneDeep(props.configs))

const { notify } = useNotifications()

const save = () => {
  form.put(route(`${routePrefix}.configs.ai.update`), {
    preserveScroll: true,
    onSuccess: () => {
      notify('success', $t('general.saved'))
    }
  })
}
</script>
<template>
  <Head title="AI" />

  <Settings>
    <form @submit.prevent="save">
      <Panel>
        <template #title>{{ $t('ai.ai') }}</template>
        <!--                <template #description>-->
        <!--                    {{ $t(('ai.description')) }}-->
        <!--                </template>-->

        <HorizontalGroup class="form-field">
          <template #title>
            <label for="provider">{{ $t('ai.ai_provider') }}</label>
          </template>
          <Select id="provider" v-model="form.provider">
            <option :value="''">-</option>
            <option v-for="(name, identifier) in providers" :key="identifier" :value="identifier">
              {{ name }}
            </option>
          </Select>
        </HorizontalGroup>

        <template v-if="!isConfigured">
          <Alert variant="warning" :closeable="false" class="form-field mt-lg">
            <p>{{ $t('ai.ai_services_not_configured') }}</p>

            <Link :href="route(`${routePrefix}.services.index`)" class="mt-md link-lighter">
              {{ $t('ai.configure_ai_service') }}
            </Link>
          </Alert>
        </template>

        <HorizontalGroup class="form-field mt-lg">
          <template #title>
            <label for="instructions">{{ $t('ai.instructions_assistant') }}</label>
          </template>
          <Textarea
            id="instructions"
            v-model="form.instructions"
            :placeholder="$t('ai.you_are_social')"
          />
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
