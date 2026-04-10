<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import PureButtonLink from '@/Components/Button/PureButtonLink.vue'
import Eye from '../../Icons/Eye.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import Trash from '../../Icons/Trash.vue'
import DropdownItem from '../Dropdown/DropdownItem.vue'
import DropdownButton from '../Dropdown/DropdownButton.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const props = defineProps({
  itemId: {
    type: Number,
    required: true
  }
})

const deletePlan = () => {
  confirmation()
    .title($t('plan.delete_plan'))
    .description($t('plan.confirm_delete_plan'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(
        route(`${routePrefix}.plans.delete`, {
          plan: props.itemId
        }),
        {
          onFinish() {
            dialog.reset()
            dialog.close()
          }
        }
      )
    })
    .show()
}
</script>
<template>
  <div>
    <div class="flex flex-row items-center justify-end gap-xs">
      <PureButtonLink
        v-tooltip="$t('general.view')"
        :href="route(`${routePrefix}.plans.view`, { plan: itemId })"
      >
        <Eye />
      </PureButtonLink>

      <Dropdown placement="bottom-end">
        <template #trigger>
          <DropdownButton />
        </template>

        <template #content>
          <DropdownItem :href="route(`${routePrefix}.plans.edit`, { plan: itemId })">
            <template #icon>
              <PencilSquare />
            </template>
            {{ $t('general.edit') }}
          </DropdownItem>

          <DropdownItem as="button" @click="deletePlan">
            <template #icon>
              <Trash class="text-red-500" />
            </template>
            {{ $t('general.delete') }}
          </DropdownItem>
        </template>
      </Dropdown>
    </div>
  </div>
</template>
