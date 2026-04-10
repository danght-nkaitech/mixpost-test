<script setup>
import { computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm } from '@inertiajs/vue3'
import { cloneDeep, find } from 'lodash'
import usePageMode from '../../../Composables/usePageMode'
import useNotifications from '../../../Composables/useNotifications'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import Input from '../../../Components/Form/Input.vue'
import Error from '../../../Components/Form/Error.vue'
import AddPlanLimit from '../../../Components/Plan/AddPlanLimit.vue'
import FeatureLimit from '../../../Components/FeatureLimit/FeatureLimit.vue'
import NoResult from '../../../Components/Util/NoResult.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Star from '../../../Icons/Star.vue'
import Select from '../../../Components/Form/Select.vue'
import PlanPriceEditor from '../../../Components/Plan/PlanPriceEditor.vue'
import Actions from '../../../Components/Plan/Actions.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')

const { isCreate, isEdit } = usePageMode()
const { notify } = useNotifications()

const pageTitle = computed(() => {
  return isCreate.value ? $t('plan.create_plan') : $t('plan.edit_plan')
})

const props = defineProps({
  featureLimitResources: {
    type: Array,
    required: true
  },
  plan: {
    type: Object,
    required: false
  }
})
const form = useForm(
  props.plan
    ? cloneDeep(props.plan)
    : {
        name: '',
        type: 'free',
        trial_days: 7,
        enabled: 1,
        sort_order: 0,
        price: {
          monthly: {
            amount: 0,
            platform_plan_id: ''
          },
          yearly: {
            amount: 0,
            platform_plan_id: ''
          }
        },
        limits: []
      }
)

const isFree = computed(() => {
  return form.type === 'free'
})

const items = computed(() => {
  return form.limits
    .map(featureLimit => {
      const resource = find(props.featureLimitResources, { code: featureLimit.code })

      if (!resource) {
        return null
      }

      return {
        resource,
        featureLimit
      }
    })
    .filter(item => item !== null)
})

const addFeatureLimit = event => {
  form.limits.push(event)
}

const removeFeatureLimit = code => {
  form.limits = form.limits.filter(item => item.code !== code)
}
const submit = () => {
  if (isCreate.value) {
    form.post(route(`${routePrefix}.plans.store`), {
      onSuccess() {
        notify('success', $t('plan.plan_created'))
      },
      onError() {
        notify('error', $t('plan.plan_not_created'))
      }
    })

    return
  }

  form.put(route(`${routePrefix}.plans.update`, { plan: props.plan.id }), {
    onSuccess() {
      notify('success', $t('plan.plan_updated'))
    },
    onError() {
      notify('error', $t('plan.plan_not_updated'))
    }
  })
}
</script>
<template>
  <Head :title="pageTitle" />

  <div class="flex flex-col grow h-full overflow-y-auto">
    <div class="flex flex-row h-full overflow-y-auto">
      <div class="w-full mx-auto row-py">
        <PageHeader :title="pageTitle">
          <template v-if="isEdit">
            <Actions :plan="plan" :edit="false" />
          </template>
          <template #description>
            {{ isEdit ? form.name : '' }}
          </template>
        </PageHeader>

        <div class="mt-lg row-px w-full">
          <Panel>
            <template #title>{{ $t('general.details') }}</template>

            <div class="w-full max-w-(--container-lg)">
              <HorizontalGroup>
                <template #title>
                  <label for="name">{{ $t('general.name') }}</label>
                </template>

                <Input
                  id="name"
                  v-model="form.name"
                  :error="form.errors.name"
                  type="text"
                  autofocus
                  class="w-full"
                  required
                />

                <template #footer>
                  <Error :message="form.errors.name" />
                </template>
              </HorizontalGroup>

              <HorizontalGroup class="mt-lg">
                <template #title>
                  <label for="type">{{ $t('general.type') }}</label>
                </template>

                <Select id="type" v-model="form.type" :disabled="isEdit" :error="form.errors.type">
                  <option value="free">{{ $t('general.free') }}</option>
                  <option value="paid">{{ $t('general.paid') }}</option>
                </Select>

                <template #footer>
                  <Error :message="form.errors.type" />
                </template>
              </HorizontalGroup>

              <HorizontalGroup class="mt-lg">
                <template #title>
                  <label for="enabled">{{ $t('general.status') }}</label>
                </template>

                <Select id="enabled" v-model="form.enabled" :error="form.errors.enabled">
                  <option :value="1">{{ $t('general.enabled') }}</option>
                  <option :value="0">{{ $t('general.disabled') }}</option>
                </Select>

                <template #footer>
                  <Error :message="form.errors.enabled" />
                </template>
              </HorizontalGroup>

              <HorizontalGroup class="mt-lg">
                <template #title>
                  <label for="sort_order">{{ $t('general.sort_order') }}</label>
                </template>

                <Input id="sort_order" v-model="form.sort_order" type="number" />

                <template #footer>
                  <Error :message="form.errors.sort_order" />
                </template>
              </HorizontalGroup>
            </div>
          </Panel>

          <template v-if="!isFree">
            <PlanPriceEditor :price="form.price" :errors="form.errors" class="mt-lg" />
          </template>

          <Panel class="mt-lg">
            <template #title>
              <div class="flex items-center">
                <Star class="mr-xs" />
                {{ $t('plan.limits') }}
              </div>
            </template>

            <template #description>{{ $t('plan.access_all_features') }} </template>

            <template #action>
              <AddPlanLimit
                :items="$page.props.featureLimitResources"
                :added-resources="form.limits.map(item => item.code)"
                @select="addFeatureLimit"
              />
            </template>

            <div class="w-full">
              <div class="flex flex-col gap-md w-full">
                <template v-for="(item, index) in items" :key="`${item.resource.code}-${index}`">
                  <FeatureLimit
                    :resource="item.resource"
                    :form="item.featureLimit.form"
                    @remove="removeFeatureLimit"
                  />
                </template>

                <NoResult v-if="!form.limits.length" />
              </div>
            </div>
          </Panel>
        </div>
      </div>
    </div>

    <div
      class="w-full flex items-center justify-end bg-stone-500 border-t border-gray-200 z-10 mt-0.5"
    >
      <div class="w-full py-md flex items-center space-x-xs row-px">
        <PrimaryButton :disabled="form.processing" :is-loading="form.processing" @click="submit">
          {{ isCreate ? $t('general.create') : $t('workspace.update') }}
        </PrimaryButton>
      </div>
    </div>
  </div>
</template>
