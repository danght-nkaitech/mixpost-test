<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import useNotifications from '../../Composables/useNotifications'
import { sortBy } from 'lodash'
import Badge from '../DataDisplay/Badge.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import Trash from '../../Icons/Trash.vue'
import DropdownItem from '../Dropdown/DropdownItem.vue'
import NoResult from '../Util/NoResult.vue'
import DropdownButton from '../Dropdown/DropdownButton.vue'
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
import AttachUser from './AttachUser.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import CanApprove from './CanApprove.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const { notify } = useNotifications()

const props = defineProps({
  workspace: {
    type: Object
  }
})

const modalRole = ref(null)

const formRole = useForm({
  user_id: null,
  role: 'admin',
  can_approve: false
})

const openModalRole = user => {
  formRole.user_id = user.id
  formRole.role = user.pivot.role
  formRole.can_approve = user.pivot.can_approve
  modalRole.value = user
}

const closeModalRole = () => {
  modalRole.value = null
  formRole.reset()
}

const detachUser = user => {
  confirmation()
    .title('Detach user')
    .description(
      `Are you sure you want to detach <strong>${user.name}</strong> from <strong>${props.workspace.name}</strong>?`
    )
    .destructive()
    .btnConfirmName($t('general.detach'))
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(route('mixpost.workspaces.users.delete', { workspace: props.workspace.uuid }), {
        data: {
          user_id: user.id
        },
        preserveScroll: true,
        onSuccess() {
          dialog.reset()
          notify('success', $t('team.user_detached'))
        },
        onFinish() {
          dialog.isLoading(false)
        }
      })
    })
    .show()
}

const updateRole = () => {
  formRole.put(route('mixpost.workspaces.users.update', { workspace: props.workspace.uuid }), {
    preserveScroll: true,
    onSuccess() {
      formRole.reset()
      closeModalRole()

      notify('success', $t('team.role_updated'))
    }
  })
}
</script>
<template>
  <Panel>
    <template #title>{{ $t('user.users') }}</template>
    <template #action>
      <AttachUser :workspace="workspace" :attached-users="workspace.users.map(item => item.id)" />
    </template>

    <template v-if="workspace.users.length">
      <Table>
        <template #head>
          <TableRow>
            <TableCell component="th" scope="col"></TableCell>
            <TableCell component="th" scope="col">{{ $t('general.name') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('general.email') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('team.role') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('team.can_approve') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('team.attached_at') }}</TableCell>
            <TableCell component="th" scope="col" />
          </TableRow>
        </template>
        <template #body>
          <template v-for="user in sortBy(workspace.users, 'pivot.joined_at')" :key="user.id">
            <TableRow :hoverable="true">
              <TableCell align="left">
                <div class="flex justify-start">
                  <Avatar :name="user.name" />
                </div>
              </TableCell>
              <TableCell>
                {{ user.name }}
              </TableCell>
              <TableCell>
                {{ user.email }}
              </TableCell>
              <TableCell>
                <Badge
                  ><span class="capitalize">{{ $t(`team.${user.pivot.role}`) }}</span></Badge
                >
              </TableCell>
              <TableCell>
                <template v-if="!user.pivot.can_approve">
                  <Badge>{{ $t('general.no') }}</Badge>
                </template>
                <template v-else>
                  <Badge variant="info">{{ $t('general.yes') }}</Badge>
                </template>
              </TableCell>
              <TableCell>
                {{ user.pivot.joined_at }}
              </TableCell>
              <TableCell align="right">
                <div class="flex justify-end">
                  <PureButtonLink
                    v-tooltip="$t('general.view')"
                    :href="route(`${routePrefix}.users.view`, { user: user.id })"
                    class="mr-xs"
                  >
                    <Eye />
                  </PureButtonLink>

                  <Dropdown placement="bottom-end">
                    <template #trigger>
                      <DropdownButton />
                    </template>

                    <template #content>
                      <DropdownItem as="button" @click="openModalRole(user)">
                        <template #icon>
                          <PencilSquare />
                        </template>

                        {{ $t('team.edit_role') }}
                      </DropdownItem>

                      <DropdownItem as="button" @click="detachUser(user)">
                        <template #icon>
                          <Trash class="text-red-500" />
                        </template>

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
      <span
        >{{ $t('team.edit_role_for') }} <span class="font-medium">{{ modalRole.name }}</span></span
      >
    </template>

    <template #body>
      <UserRole v-model="formRole.role" class="mt-xs" />
      <VerticalGroup class="mt-lg">
        <CanApprove v-model="formRole.can_approve" />
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
        {{ $t('general.update') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
