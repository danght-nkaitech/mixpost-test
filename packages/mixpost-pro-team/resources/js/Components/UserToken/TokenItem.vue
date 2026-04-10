<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import emitter from '@/Services/emitter'
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import PureDangerButton from '../Button/PureDangerButton.vue'
import Trash from '../../Icons/Trash.vue'
import useRouter from '../../Composables/useRouter'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const { onError } = useRouter()

const openDeleteTokenConfirmation = () => {
  confirmation()
    .title($t('access_token.delete'))
    .description($t('access_token.delete_confirm'))
    .destructive()
    .onConfirm(dialog => {
      deleteTokenAfterConfirmed(dialog)
    })
    .show()
}

const deleteTokenAfterConfirmed = dialog => {
  dialog.isLoading(true)

  router.delete(route(`${routePrefix}.profile.accessTokens.delete`, { token: props.item.id }), {
    onSuccess() {
      dialog.reset()
      emitter.emit('tokenDelete', props.item.id)
    },
    onError(errors) {
      onError(errors, () => {
        deleteTokenAfterConfirmed(dialog)
      })
    },
    onFinish() {
      dialog.isLoading(false)
    }
  })
}
</script>
<template>
  <TableRow :hoverable="true">
    <TableCell class="w-10">
      <slot name="checkbox" />
    </TableCell>

    <TableCell>
      {{ item.name }}
    </TableCell>

    <TableCell>
      {{ item.last_used_at ? item.last_used_at : $t('access_token.never_used') }}
    </TableCell>

    <TableCell>
      {{ item.expires_at }}
    </TableCell>

    <TableCell>
      {{ item.created_at }}
    </TableCell>

    <TableCell>
      <PureDangerButton v-tooltip="$t('general.delete')" @click="openDeleteTokenConfirmation">
        <Trash class="w-5! h-5!" />
      </PureDangerButton>
    </TableCell>
  </TableRow>
</template>
