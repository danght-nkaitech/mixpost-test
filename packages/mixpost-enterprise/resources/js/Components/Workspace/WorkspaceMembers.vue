<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useForm, router } from '@inertiajs/vue3'
import useNotifications from '../../Composables/useNotifications'
import useAuth from '../../Composables/useAuth'
import Badge from '../DataDisplay/Badge.vue'
import Dropdown from '../Dropdown/Dropdown.vue'
import PencilSquare from '../../Icons/PencilSquare.vue'
import Trash from '../../Icons/Trash.vue'
import X from '../../Icons/X.vue'
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
import InviteMember from './InviteMember.vue'
import Flex from '../Layout/Flex.vue'
import PureButton from '../Button/PureButton.vue'
import QuestionMarkCircle from '../../Icons/QuestionMarkCircle.vue'
import CanApprove from './CanApprove.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'

const { t: $t } = useI18n()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const { notify } = useNotifications()
const { user: authUser } = useAuth()

const props = defineProps({
  workspace: {
    required: true,
    type: Object
  },
  users: {
    required: true,
    type: Array
  },
  invitations: {
    required: true,
    type: Array
  }
})

const modalRole = ref(null)

const formUserRole = useForm({
  user_id: null,
  role: 'admin',
  can_approve: false
})

const openModalRole = user => {
  formUserRole.user_id = user.id
  formUserRole.role = user.pivot.role
  formUserRole.can_approve = user.pivot.can_approve
  modalRole.value = user
}

const closeModalRole = () => {
  modalRole.value = null
  formUserRole.reset()
}

const detachUser = user => {
  confirmation()
    .title($t('user.detach_user'))
    .description($t('user.detach_user_desc', { user: user.name, workspace: props.workspace.name }))
    .destructive()
    .btnConfirmName('Remove')
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(
        route(`${routePrefix}.workspace.users.detach`, {
          workspace: props.workspace.uuid,
          user: user.id
        }),
        {
          preserveScroll: true,
          onSuccess() {
            dialog.reset()
            notify('success', $t('user.user_detached'))
          },
          onFinish() {
            dialog.isLoading(false)
          }
        }
      )
    })
    .show()
}

const updateUserRole = () => {
  formUserRole.put(
    route(`${routePrefix}.workspace.users.updateRole`, {
      workspace: props.workspace.uuid,
      user: formUserRole.user_id
    }),
    {
      preserveScroll: true,
      onSuccess() {
        formUserRole.reset()
        closeModalRole()

        notify('success', $t('team.role_updated'))
      }
    }
  )
}

const cancelInvitation = invitation => {
  confirmation()
    .title($t('team.cancel_invitation'))
    .description($t('team.confirm_cancel_invitation', { email: invitation.email }))
    .destructive()
    .btnConfirmName('Confirm')
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(
        route(`${routePrefix}.workspace.invitations.cancel`, {
          workspace: props.workspace.uuid,
          invitation: invitation.uuid
        }),
        {
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
</script>
<template>
  <Panel>
    <template #title>{{ $t('team.members') }}</template>
    <template #description>
      {{ $t('team.invite_members') }}
    </template>
    <template #action>
      <InviteMember :workspace="workspace" />
    </template>

    <template v-if="users.length || invitations.length">
      <Table>
        <template #head>
          <TableRow>
            <TableCell component="th" scope="col"></TableCell>
            <TableCell component="th" scope="col">{{ $t('general.name') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('general.email') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('team.role') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('team.can_approve') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('general.joined_at') }}</TableCell>
            <TableCell component="th" scope="col" />
          </TableRow>
        </template>
        <template #body>
          <template v-for="invitation in invitations" :key="invitation.uuid">
            <TableRow :hoverable="true">
              <TableCell align="left">
                <div class="flex justify-start">
                  <Avatar :name="invitation.email" rounded-class="rounded-lg" />
                </div>
              </TableCell>
              <TableCell>
                <Flex class="justify-between">
                  <Badge variant="warning"> {{ $t('team.pending') }}</Badge>
                  <PureButton
                    v-tooltip="
                      `
                                         ${$t('team.invited_by', { user: invitation.author.name })}. \n
                                         ${$t('team.invited_on', { date: invitation.created_at })}
                                    `
                    "
                  >
                    <QuestionMarkCircle />
                  </PureButton>
                </Flex>
              </TableCell>
              <TableCell>
                {{ invitation.email }}
              </TableCell>
              <TableCell>
                <Badge>{{ $t(`team.${invitation.role}`) }}</Badge>
              </TableCell>
              <TableCell>
                <template v-if="!invitation.can_approve">
                  <Badge>{{ $t('general.no') }}</Badge>
                </template>
                <template v-else>
                  <Badge variant="info">{{ $t('general.yes') }}</Badge>
                </template>
              </TableCell>
              <TableCell>-</TableCell>
              <TableCell align="right">
                <Dropdown placement="bottom-end">
                  <template #trigger>
                    <DropdownButton />
                  </template>

                  <template #content>
                    <!--                                        <DropdownItem as="button">-->
                    <!--                                            <PencilSquare class="mr-xs"/>-->
                    <!--                                            Edit role-->
                    <!--                                        </DropdownItem>-->

                    <DropdownItem as="button" @click="cancelInvitation(invitation)">
                      <X class="text-red-500 mr-xs" />
                      {{ $t('general.cancel') }}
                    </DropdownItem>
                  </template>
                </Dropdown>
              </TableCell>
            </TableRow>
          </template>

          <template v-for="user in users" :key="user.id">
            <TableRow :hoverable="true">
              <TableCell align="left">
                <div class="flex justify-start">
                  <Avatar
                    :background-color="user.hex_color"
                    :name="user.name"
                    rounded-class="rounded-lg"
                  />
                </div>
              </TableCell>
              <TableCell>
                {{ user.name }}
              </TableCell>
              <TableCell>
                {{ user.email }}
              </TableCell>
              <TableCell>
                <template v-if="workspace.owner_id === user.id">
                  <Badge variant="warning">{{ $t('general.owner') }}</Badge>
                </template>
                <template v-else>
                  <Badge>{{ $t(`team.${user.pivot.role}`) }}</Badge>
                </template>
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
                <template v-if="authUser.id !== user.id && workspace.owner_id !== user.id">
                  <Dropdown placement="bottom-end">
                    <template #trigger>
                      <DropdownButton />
                    </template>

                    <template #content>
                      <DropdownItem as="button" @click="openModalRole(user)">
                        <PencilSquare class="mr-xs" />
                        {{ $t('user.edit_role') }}
                      </DropdownItem>

                      <DropdownItem as="button" @click="detachUser(user)">
                        <Trash class="text-red-500 mr-xs" />
                        {{ $t('general.remove') }}
                      </DropdownItem>
                    </template>
                  </Dropdown>
                </template>
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
      <i18n-t keypath="team.edit_role_for" tag="span" scope="global">
        <template #user>
          <span class="font-medium">{{ modalRole.name }}</span>
        </template>
      </i18n-t>
    </template>

    <template #body>
      <UserRole v-model="formUserRole.role" class="mt-xs" />
      <VerticalGroup class="mt-lg">
        <CanApprove v-model="formUserRole.can_approve" />
      </VerticalGroup>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="closeModalRole">{{
        $t('general.cancel')
      }}</SecondaryButton>
      <PrimaryButton
        :disabled="formUserRole.processing"
        :is-loading="formUserRole.processing"
        @click="updateUserRole"
      >
        {{ $t('workspace.update') }}
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
