<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link, router } from '@inertiajs/vue3'
import useAuth from '../../Composables/useAuth'
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
  user: {
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

const { user: authUser } = useAuth()

const destroy = () => {
  confirmation()
    .title($t('user.delete_user'))
    .description($t('user.confirm_delete_user'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(route(`${routePrefix}.users.delete`, { user: props.user.id }), {
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
    <template v-if="authUser.id !== user.id">
      <Link
        :href="route(`${routePrefix}.impersonate.start`, { user: user.id })"
        method="post"
        as="button"
        type="button"
      >
        <SecondaryButton>
          {{ $t('user.impersonate') }}
        </SecondaryButton>
      </Link>
    </template>

    <template v-if="view">
      <Link :href="route(`${routePrefix}.users.view`, { user: user.id })">
        <SecondaryButton size="sm">
          <template #icon>
            <Eye />
          </template>
          {{ $t('general.view') }}
        </SecondaryButton>
      </Link>
    </template>

    <template v-if="create">
      <Link :href="route(`${routePrefix}.users.create`)">
        <PrimaryButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <Plus />
          </template>
          {{ $t('general.create') }}
        </PrimaryButton>
      </Link>
    </template>

    <template v-if="edit">
      <Link :href="route(`${routePrefix}.users.edit`, { user: user.id })">
        <PrimaryButton size="sm" :hidden-text-on-small-screen="true">
          <template #icon>
            <PencilSquare />
          </template>
          {{ $t('general.edit') }}
        </PrimaryButton>
      </Link>
    </template>

    <template v-if="destroy && authUser.id !== user.id">
      <DangerButton size="sm" @click="destroy">
        <template #icon>
          <Trash />
        </template>
      </DangerButton>
    </template>
  </Flex>
</template>
