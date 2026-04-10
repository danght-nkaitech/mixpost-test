<script setup>
import { inject, onMounted, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import Flex from '../Layout/Flex.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import DialogModal from '../Modal/DialogModal.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import Radio from '../Form/Radio.vue'
import LabelGroup from '../Surface/LabelGroup.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import Error from '../Form/Error.vue'
import Input from '../Form/Input.vue'
import NoResult from '../Util/NoResult.vue'
import WarningButton from '../Button/WarningButton.vue'
import Alert from '../Util/Alert.vue'
import Checkbox from '../Form/Checkbox.vue'
import Badge from '../DataDisplay/Badge.vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true
  },
  plans: {
    type: Array,
    required: true
  }
})

const form = useForm({
  trial_days: null,
  plan_id: null,
  keep_prev_trial_ends_at: true
})

const routePrefix = inject('routePrefix')

const add = () => {
  form.post(
    route(`${routePrefix}.workspaces.subscription.addGeneric`, { workspace: props.workspace.uuid }),
    {
      preserveScroll: true,
      onSuccess: () => {
        closeModal()
        form.reset()
      }
    }
  )
}

const selectFirstPlan = () => {
  if (props.plans.length > 0) {
    form.plan_id = props.plans[0].id
  }
}

onMounted(() => {
  selectFirstPlan()
})

const modal = ref(false)

const openModal = () => {
  modal.value = true

  selectFirstPlan()
}

const closeModal = () => {
  if (form.processing) {
    return
  }

  modal.value = false
}
</script>

<template>
  <WarningButton @click="openModal">{{ $t('subscription.add_sub') }}</WarningButton>

  <DialogModal :show="modal" max-width="md" @close="closeModal">
    <template #header>{{ $t('subscription.add_sub') }}</template>

    <template #body>
      <template v-if="plans.length">
        <Alert variant="warning" :closeable="false">
          {{ $t('subscription.cancel_delete_sub') }}
        </Alert>

        <VerticalGroup class="mt-lg">
          <template #title> {{ $t('plan.plan') }}</template>

          <Flex :col="true" class="w-full">
            <template v-for="plan in plans" :key="plan.id">
              <LabelGroup :active="plan.id === form.plan_id">
                <Radio v-model:checked="form.plan_id" :value="plan.id" />
                <span class="ml-xs">
                  <span class="mr-xs">{{ plan.name }}</span>
                  <template v-if="!plan.enabled">
                    <Badge variant="error">{{ $t('general.disabled') }}</Badge>
                  </template>
                </span>
              </LabelGroup>
            </template>
          </Flex>
        </VerticalGroup>

        <div class="mt-lg">
          <label>
            <Checkbox v-model:checked="form.keep_prev_trial_ends_at" />
            {{ $t('subscription.keep_sub_trial') }}
          </label>
        </div>

        <template v-if="!form.keep_prev_trial_ends_at">
          <VerticalGroup class="mt-lg">
            <template #title> {{ $t('subscription.trial_days') }}</template>
            <div class="w-full">
              <div class="text-gray-500 mb-1">{{ $t('subscription.empty_config') }}</div>
              <Input v-model="form.trial_days" type="number" />
            </div>
          </VerticalGroup>
        </template>

        <Error v-for="(error, key) in form.errors" :key="key" :message="error" class="mb-xs" />
      </template>

      <template v-if="!plans.length">
        <NoResult>{{ $t('plan.no_plans') }}</NoResult>

        <div class="mt-lg">
          <Link :href="route(`${routePrefix}.plans.create`)">
            <PrimaryButton>{{ $t('plan.create_plan') }}</PrimaryButton>
          </Link>
        </div>
      </template>
    </template>

    <template #footer>
      <Flex>
        <SecondaryButton @click="closeModal">
          {{ $t('general.cancel') }}
        </SecondaryButton>

        <template v-if="plans.length">
          <PrimaryButton :disabled="form.processing" :is-loading="form.processing" @click="add">
            {{ $t('general.add') }}
          </PrimaryButton>
        </template>
      </Flex>
    </template>
  </DialogModal>
</template>
