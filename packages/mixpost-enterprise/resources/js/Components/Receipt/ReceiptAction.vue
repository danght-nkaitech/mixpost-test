<script setup>
import { inject } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Trash from '../../Icons/Trash.vue'
import DangerButton from '../Button/DangerButton.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import ArrowDownTray from '../../Icons/ArrowDownTray.vue'
import Flex from '../Layout/Flex.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import Eye from '../../Icons/Eye.vue'
import Plus from '../../Icons/Plus.vue'
import SuccessButton from '../Button/SuccessButton.vue'
import { useI18n } from 'vue-i18n'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const props = defineProps({
  receipt: {
    type: Object
  },
  view: {
    type: Boolean,
    default: true
  },
  create: {
    type: Boolean,
    default: true
  },
  edit: {
    type: Boolean,
    default: true
  },
  download: {
    type: Boolean,
    default: true
  },
  destroy: {
    type: Boolean,
    default: true
  }
})

const deleteReceipt = () => {
  confirmation()
    .title($t('finance.delete_receipt'))
    .description($t('finance.confirm_delete_receipt'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(route(`${routePrefix}.receipts.delete`, { receipt: props.receipt.uuid }), {
        preserveScroll: true,
        onFinish() {
          dialog.reset()
        }
      })
    })
    .show()
}
</script>
<template>
  <Flex :responsive="false" class="items-center">
    <template v-if="create">
      <Link :href="route(`${routePrefix}.receipts.create`)">
        <PrimaryButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <Plus />
          </template>
          {{ $t('general.create') }}
        </PrimaryButton>
      </Link>
    </template>

    <template v-if="view">
      <Link :href="route(`${routePrefix}.receipts.view`, { receipt: receipt.uuid })">
        <SecondaryButton size="sm">
          <template #icon>
            <Eye />
          </template>
          {{ $t('general.view') }}
        </SecondaryButton>
      </Link>
    </template>

    <template v-if="edit">
      <Link :href="route(`${routePrefix}.receipts.edit`, { receipt: receipt.uuid })">
        <PrimaryButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <PencilSquare />
          </template>
          {{ $t('general.edit') }}
        </PrimaryButton>
      </Link>
    </template>

    <template v-if="download">
      <a
        :href="
          receipt.receipt_url
            ? receipt.receipt_url
            : route(`${routePrefix}.receipts.download`, { receipt: receipt.uuid })
        "
        target="_blank"
      >
        <SuccessButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <ArrowDownTray />
          </template>
          {{ $t('general.download') }}
        </SuccessButton>
      </a>
    </template>

    <template v-if="destroy">
      <DangerButton size="sm" @click="deleteReceipt">
        <template #icon>
          <Trash />
        </template>
      </DangerButton>
    </template>
  </Flex>
</template>
