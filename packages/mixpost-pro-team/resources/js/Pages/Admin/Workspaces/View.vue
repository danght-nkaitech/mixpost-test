<script setup>
import { inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import useNotifications from '../../../Composables/useNotifications'
import AdminLayout from '@/Layouts/Admin.vue'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import DangerButton from '../../../Components/Button/DangerButton.vue'
import WorkspaceUsers from '../../../Components/Workspace/WorkspaceUsers.vue'
import PencilSquare from '../../../Icons/PencilSquare.vue'
import Trash from '../../../Icons/Trash.vue'
import ArrowTopRightOnSquare from '../../../Icons/ArrowTopRightOnSquare.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import ClipboardCard from '../../../Components/Util/ClipboardCard.vue'

defineOptions({ layout: AdminLayout })

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const props = defineProps({
  workspace: {
    type: Object
  }
})

const { notify } = useNotifications()

const deleteWorkspace = () => {
  confirmation()
    .title($t('workspace.delete_workspace'))
    .description($t('workspace.confirm_delete_workspace'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(
        route(`${routePrefix}.workspaces.delete`, { workspace: props.workspace.uuid }),
        {
          onSuccess() {
            notify('success', $t('workspace.workspace_deleted'))
          }
        }
      )
    })
    .show()
}
</script>
<template>
  <Head :title="$t('workspace.view_workspace')" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="$t('workspace.view_workspace')">
      <template #description>
        {{ workspace.name }}
      </template>
      <div class="flex items-center">
        <Link
          :href="route('mixpost.switchWorkspace', { workspace: workspace.uuid, redirect: true })"
          method="post"
          as="button"
          type="button"
          class="mr-xs rtl:mr-0 rtl:ml-xs"
        >
          <SecondaryButton size="sm">
            <template #icon>
              <ArrowTopRightOnSquare />
            </template>
            {{ $t('general.open') }}
          </SecondaryButton>
        </Link>

        <Link
          :href="route(`${routePrefix}.workspaces.edit`, { workspace: workspace.uuid })"
          class="mr-xs rtl:mr-0 rtl:ml-xs"
        >
          <PrimaryButton size="sm">
            <template #icon>
              <PencilSquare />
            </template>
            {{ $t('general.edit') }}
          </PrimaryButton>
        </Link>

        <DangerButton size="sm" @click="deleteWorkspace">
          <template #icon>
            <Trash />
          </template>
        </DangerButton>
      </div>
    </PageHeader>

    <div class="row-px">
      <Panel>
        <template #title>{{ $t('general.details') }}</template>

        <HorizontalGroup>
          <template #title>
            {{ $t('general.name') }}
          </template>

          <div>{{ workspace.name }}</div>
        </HorizontalGroup>

        <HorizontalGroup class="mt-lg">
          <template #title>
            {{ $t('general.color') }}
          </template>

          <div :style="{ background: workspace.hex_color }" class="w-xl h-xl rounded-md" />
        </HorizontalGroup>
      </Panel>

      <div class="mt-lg">
        <WorkspaceUsers :workspace="workspace" />
      </div>

      <template v-if="$page.props.mixpost.features.api_access_tokens">
        <Panel class="mt-lg">
          <template #title>
            {{ $t('system.usage_api') }}
          </template>

          <ClipboardCard>{{ workspace.uuid }}</ClipboardCard>
        </Panel>
      </template>
    </div>
  </div>
</template>
