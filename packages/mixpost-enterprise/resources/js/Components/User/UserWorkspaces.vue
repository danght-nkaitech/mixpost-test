<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useForm, router } from '@inertiajs/vue3'
import Badge from '../DataDisplay/Badge.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import Trash from '../../Icons/Trash.vue'
import DropdownItem from '../Dropdown/DropdownItem.vue'
import NoResult from '../Util/NoResult.vue'
import DropdownButton from '../Dropdown/DropdownButton.vue'
import AttachWorkspace from './AttachWorkspace.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import Panel from '../Surface/Panel.vue'
import Table from '@/Components/DataDisplay/Table.vue'
import TableRow from '@/Components/DataDisplay/TableRow.vue'
import TableCell from '@/Components/DataDisplay/TableCell.vue'
import Avatar from '../DataDisplay/Avatar.vue'
import UserRole from '../Workspace/UserRole.vue'
import DialogModal from '../Modal/DialogModal.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import Eye from '../../Icons/Eye.vue'
import PureButtonLink from '../Button/PureButtonLink.vue'
import Flex from '../Layout/Flex.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import CanApprove from '../Workspace/CanApprove.vue'
import useNotifications from '../../Composables/useNotifications.js'
import IsOwner from '../Workspace/IsOwner.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const props = defineProps({
  user: {
    type: Object
  }
})

const { notify } = useNotifications()

const modalRole = ref(null)

const formRole = useForm({
  user_id: props.user.id,
  role: 'admin',
  can_approve: false,
  is_owner: false
})

const openModalRole = workspace => {
  formRole.role = workspace.pivot.role
  formRole.can_approve = workspace.pivot.can_approve
  formRole.is_owner = workspace.owner_id === props.user.id
  modalRole.value = workspace
}

const closeModalRole = () => {
  modalRole.value = null
}

const detachWorkspace = workspace => {
  confirmation()
    .title($t('workspace.detach_workspace'))
    .description(
      $t('workspace.detach_workspace_desc', { workspace: workspace.name, user: props.user.name })
    )
    .destructive()
    .btnConfirmName('Detach')
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(
        route(`${routePrefix}.workspaces.users.delete`, { workspace: workspace.uuid }),
        {
          data: {
            user_id: props.user.id
          },
          preserveScroll: true,
          onSuccess() {
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

const updateRole = () => {
  formRole.put(
    route(`${routePrefix}.workspaces.users.update`, { workspace: modalRole.value.uuid }),
    {
      data: {
        user_id: props.user.id
      },
      preserveScroll: true,
      onSuccess() {
        formRole.reset()
        closeModalRole()

        notify('success', $t('team.role_updated'))
      }
    }
  )
}
</script>
<template>
  <Panel>
    <template #title>{{ $t('workspace.workspaces') }}</template>
    <template #action>
      <AttachWorkspace :user="user" :attached-workspaces="user.workspaces.map(item => item.uuid)" />
    </template>

    <template v-if="user.workspaces.length">
      <Table>
        <template #head>
          <TableRow>
            <TableCell component="th" scope="col"></TableCell>
            <TableCell component="th" scope="col">{{ $t('general.name') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('team.role') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('team.can_approve') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('general.joined_at') }}</TableCell>
            <TableCell component="th" scope="col" />
          </TableRow>
        </template>
        <template #body>
          <template v-for="workspace in user.workspaces" :key="workspace.id">
            <TableRow :hoverable="true">
              <TableCell align="left">
                <div class="flex justify-start">
                  <Avatar
                    :background-color="workspace.hex_color"
                    :name="workspace.name"
                    rounded-class="rounded-lg"
                  />
                </div>
              </TableCell>
              <TableCell>
                <Flex>
                  <div>{{ workspace.name }}</div>
                  <template v-if="user.id === workspace.owner_id">
                    <Badge variant="warning"> {{ $t('general.owner') }}</Badge>
                  </template>
                </Flex>
              </TableCell>
              <TableCell>
                <Badge>{{ $t(`team.${workspace.pivot.role}`) }}</Badge>
              </TableCell>
              <TableCell>
                <template v-if="!workspace.pivot.can_approve">
                  <Badge>{{ $t('general.no') }}</Badge>
                </template>
                <template v-else>
                  <Badge variant="info">{{ $t('general.yes') }}</Badge>
                </template>
              </TableCell>
              <TableCell>
                {{ workspace.pivot.joined_at }}
              </TableCell>
              <TableCell align="right">
                <div class="flex justify-end">
                  <PureButtonLink
                    v-tooltip="$t('general.view')"
                    :href="route(`${routePrefix}.workspaces.view`, { workspace: workspace.uuid })"
                    class="mr-xs"
                  >
                    <Eye />
                  </PureButtonLink>

                  <Dropdown placement="bottom-end">
                    <template #trigger>
                      <DropdownButton />
                    </template>

                    <template #content>
                      <DropdownItem as="button" @click="openModalRole(workspace)">
                        <PencilSquare class="mr-xs" />
                        {{ $t('user.edit_role') }}
                      </DropdownItem>

                      <DropdownItem as="button" @click="detachWorkspace(workspace)">
                        <Trash class="text-red-500 mr-xs" />
                        {{ $t('general.detach') }}
                      </DropdownItem>
                    </template>
                  </Dropdown>
                </div>
              </TableCell>
            </TableRow>
          </template>
        </template>
      </Table>
    </template>

    <template v-else>
      <NoResult />
    </template>
  </Panel>

  <DialogModal
    :show="modalRole !== null"
    max-width="md"
    :scrollable-body="true"
    :closeable="true"
    @close="closeModalRole"
  >
    <template #header>
      <i18n-t keypath="user.edit_role_on" tag="span" scope="global">
        <template #workspace>
          <span class="font-medium">{{ modalRole.name }}</span>
        </template>
      </i18n-t>
    </template>

    <template #body>
      <UserRole v-model="formRole.role" class="mt-xs" />

      <VerticalGroup class="mt-lg">
        <CanApprove v-model="formRole.can_approve" />
      </VerticalGroup>

      <VerticalGroup class="mt-lg">
        <IsOwner v-model="formRole.is_owner" />
      </VerticalGroup>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="closeModalRole">{{
        $t('general.cancel')
      }}</SecondaryButton>
      <PrimaryButton
        :disabled="formRole.processing"
        :is-loading="formRole.processing"
        @click="updateRole"
      >
        {{ $t('workspace.update') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
