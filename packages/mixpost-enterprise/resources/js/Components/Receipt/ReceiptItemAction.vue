<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import emitter from '@/Services/emitter'
import { router } from '@inertiajs/vue3'
import PureButtonLink from '@/Components/Button/PureButtonLink.vue'
import Eye from '../../Icons/Eye.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import Trash from '../../Icons/Trash.vue'
import DropdownItem from '../Dropdown/DropdownItem.vue'
import DropdownButton from '../Dropdown/DropdownButton.vue'
import ArrowDownTray from '../../Icons/ArrowDownTray.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const props = defineProps({
  itemId: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['onDelete'])

const confirmationDeletion = ref(false)

const deleteReceipt = () => {
  confirmation()
    .title($t('finance.delete_receipt'))
    .description($t('finance.confirm_delete_receipt'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(
        route(`${routePrefix}.receipts.delete`, {
          receipt: props.itemId
        }),
        {
          onSuccess() {
            confirmationDeletion.value = false
            emit('onDelete')
            emitter.emit('workspaceDelete', props.itemId)
            dialog.reset()
          },
          onFinish() {
            dialog.isLoading(false)
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
        :href="route(`${routePrefix}.receipts.view`, { receipt: itemId })"
      >
        <Eye />
      </PureButtonLink>

      <Dropdown width-classes="w-36" placement="bottom-end">
        <template #trigger>
          <DropdownButton />
        </template>

        <template #content>
          <DropdownItem
            as="a"
            :href="route(`${routePrefix}.receipts.download`, { receipt: itemId })"
          >
            <template #icon>
              <ArrowDownTray />
            </template>
            {{ $t('general.download') }}
          </DropdownItem>

          <DropdownItem :href="route(`${routePrefix}.receipts.edit`, { receipt: itemId })">
            <template #icon>
              <PencilSquare />
            </template>
            {{ $t('general.edit') }}
          </DropdownItem>

          <DropdownItem as="button" @click="deleteReceipt">
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
