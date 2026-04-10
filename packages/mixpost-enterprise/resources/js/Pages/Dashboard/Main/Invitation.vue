<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, router } from '@inertiajs/vue3'
import MinimalLayout from '@/Layouts/Minimal.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import PageHeader from '../../../Components/DataDisplay/PageHeader.vue'
import BackToDashboardButton from '../../../Components/Helper/BackToDashboardButton.vue'

defineOptions({ layout: MinimalLayout })

const props = defineProps({
  invitation: {
    type: Object,
    required: true
  }
})

const { t: $t } = useI18n()

const title = computed(() => {
  return $t('team.join_workspace', { workspace: props.invitation.workspace.name })
})

const routePrefix = inject('routePrefix')

const isLoading = ref(false)
const acceptLoading = ref(false)
const declineLoading = ref(false)

const decline = () => {
  isLoading.value = true
  declineLoading.value = true
  router.delete(
    route(`${routePrefix}.invitations.decline`, { invitation: props.invitation.uuid }),
    {},
    {
      onFinish: () => {
        isLoading.value = false
        declineLoading.value = false
      }
    }
  )
}

const accept = () => {
  isLoading.value = true
  acceptLoading.value = true
  router.post(
    route(`${routePrefix}.invitations.accept`, { invitation: props.invitation.uuid }),
    {},
    {
      onFinish: () => {
        isLoading.value = false
        acceptLoading.value = false
      }
    }
  )
}
</script>
<template>
  <Head :title="title" />

  <div class="w-full">
    <PageHeader class="px-0!" title="">
      <BackToDashboardButton />
    </PageHeader>

    <Panel>
      <template #title>{{
        $t('team.join_workspace', { workspace: invitation.workspace.name })
      }}</template>
      <template #description>
        {{
          $t('team.join_workspace_desc', {
            user: invitation.author.name,
            workspace: invitation.workspace.name
          })
        }}
      </template>

      <Flex :responsive="false">
        <SecondaryButton :disabled="isLoading" :is-loading="declineLoading" @click="decline"
          >{{ $t('general.decline') }}
        </SecondaryButton>

        <PrimaryButton :disabled="isLoading" :is-loading="acceptLoading" @click="accept"
          >{{ $t('general.accept') }}
        </PrimaryButton>
      </Flex>
    </Panel>
  </div>
</template>
