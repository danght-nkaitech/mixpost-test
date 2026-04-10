<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link, router } from '@inertiajs/vue3'
import Trash from '../../Icons/Trash.vue'
import DangerButton from '../Button/DangerButton.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import Flex from '../Layout/Flex.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import Eye from '../../Icons/Eye.vue'
import Plus from '../../Icons/Plus.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const props = defineProps({
  workspace: {
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
  destroy: {
    type: Boolean,
    default: true
  }
})

const destroy = () => {
  confirmation()
    .title($t('workspace.delete_workspace'))
    .description(
      `${$t('workspace.confirm_delete_workspace')}<br><br>${$t('workspace.data_delete')}`
    )
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(
        route(`${routePrefix}.workspaces.delete`, { workspace: props.workspace.uuid }),
        {
          preserveScroll: true,
          onFinish() {
            dialog.reset()
          }
        }
      )
    })
    .show()
}
</script>
<template>
  <Flex :responsive="false" class="items-center">
    <template v-if="create">
      <Link :href="route(`${routePrefix}.workspaces.create`)">
        <PrimaryButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <Plus />
          </template>
          {{ $t('general.create') }}
        </PrimaryButton>
      </Link>
    </template>

    <template v-if="view">
      <Link :href="route(`${routePrefix}.workspaces.view`, { workspace: workspace.uuid })">
        <SecondaryButton size="sm">
          <template #icon>
            <Eye />
          </template>
          {{ $t('general.view') }}
        </SecondaryButton>
      </Link>
    </template>

    <template v-if="edit">
      <Link :href="route(`${routePrefix}.workspaces.edit`, { workspace: workspace.uuid })">
        <PrimaryButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <PencilSquare />
          </template>
          {{ $t('general.edit') }}
        </PrimaryButton>
      </Link>
    </template>

    <template v-if="destroy">
      <DangerButton size="sm" @click="destroy">
        <template #icon>
          <Trash />
        </template>
      </DangerButton>
    </template>
  </Flex>
</template>
