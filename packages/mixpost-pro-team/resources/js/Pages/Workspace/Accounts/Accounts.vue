<script setup>
import { inject, ref } from 'vue'
import { router, useForm, Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import useNotifications from '@/Composables/useNotifications'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import Modal from '@/Components/Modal/Modal.vue'
import ConfirmationModal from '@/Components/Modal/ConfirmationModal.vue'
import Account from '@/Components/Account/Account.vue'
import AddTwitterAccount from '@/Components/Account/AddTwitterAccount.vue'
import AddFacebookPage from '@/Components/Account/AddFacebookPage.vue'
import AddMastodonAccount from '@/Components/Account/AddMastodonAccount.vue'
import AddInstagramAccount from '@/Components/Account/AddInstagramAccount.vue'
import AddThreadsAccount from '@/Components/Account/AddThreadsAccount.vue'
import AddYoutubeAccount from '@/Components/Account/AddYoutubeAccount.vue'
import AddGBPAccount from '@/Components/Account/AddGBPAccount.vue'
import AddPinterestAccount from '@/Components/Account/AddPinterestAccount.vue'
import AddLinkedinProfile from '@/Components/Account/AddLinkedinProfile.vue'
import AddLinkedinPage from '@/Components/Account/AddLinkedinPage.vue'
import AddTikTokAccount from '@/Components/Account/AddTikTokAccount.vue'
import AddBlueskyAccount from '@/Components/Account/AddBlueskyAccount.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import DangerButton from '@/Components/Button/DangerButton.vue'
import Dropdown from '@/Components/Dropdown/Dropdown.vue'
import DropdownItem from '@/Components/Dropdown/DropdownItem.vue'
import GridAddElement from '@/Components/DataDisplay/GridAddElement.vue'
import EllipsisVerticalIcon from '@/Icons/EllipsisVertical.vue'
import RefreshIcon from '@/Icons/Refresh.vue'
import TrashIcon from '@/Icons/Trash.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import PureButton from '@/Components/Button/PureButton.vue'
import PencilSquare from '../../../Icons/PencilSquare.vue'
import DialogModal from '../../../Components/Modal/DialogModal.vue'
import Input from '../../../Components/Form/Input.vue'
import AlertUnconfiguredService from '../../../Components/Util/AlertUnconfiguredService.vue'
import Alert from '../../../Components/Util/Alert.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import useRequestErrorsHandler from '../../../Composables/useRequestErrorsHandler.js'
import ArrowTopRightOnSquare from '@/Icons/ArrowTopRightOnSquare.vue'
import MenuDelimiter from '@/Components/Sidebar/MenuDelimiter.vue'
import DropdownGroupTitle from '@/Components/Dropdown/DropdownGroupTitle.vue'

const { t: $t } = useI18n()

const workspaceCtx = inject('workspaceCtx')
const routePrefix = inject('routePrefix')

const title = $t('account.accounts')

const { notify } = useNotifications()

const addAccountModal = ref(false)
const confirmationAccountDeletion = ref(null)
const accountIsDeleting = ref(false)

const updateAccount = accountUuid => {
  router.put(
    route('mixpost.accounts.update', {
      workspace: workspaceCtx.id,
      account: accountUuid
    }),
    {},
    {
      preserveScroll: true,
      onSuccess(response) {
        if (response.props.flash.error) {
          return
        }

        notify('success', $t('account.account_updated'))
      }
    }
  )
}

const deleteAccount = () => {
  router.delete(
    route(`${routePrefix}.accounts.delete`, {
      workspace: workspaceCtx.id,
      account: confirmationAccountDeletion.value
    }),
    {
      preserveScroll: true,
      onStart() {
        accountIsDeleting.value = true
      },
      onSuccess() {
        confirmationAccountDeletion.value = null
        notify('success', $t('account.account_deleted'))
      },
      onFinish() {
        accountIsDeleting.value = false
      }
    }
  )
}

const closeConfirmationAccountDeletion = () => {
  if (accountIsDeleting.value) {
    return
  }

  confirmationAccountDeletion.value = null
}

const showSuffixForm = ref(false)
const suffixForm = useForm({
  uuid: null,
  name: ''
})

// Account Suffix
const openSuffixForm = account => {
  showSuffixForm.value = true
  suffixForm.uuid = account.uuid
  suffixForm.name = account.suffix
}

const closeSuffixForm = () => {
  showSuffixForm.value = null
  suffixForm.reset()
}

const updateSuffix = () => {
  suffixForm.put(
    route(`${routePrefix}.accounts.updateSuffix`, {
      workspace: workspaceCtx.id,
      account: suffixForm.uuid
    }),
    {
      onSuccess() {
        closeSuffixForm()
      }
    }
  )
}

useRequestErrorsHandler()
</script>
<template>
  <Head :title="title" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="title">
      <template #description>
        {{ $t('account.connect_social_account') }}
      </template>
    </PageHeader>
    <div class="mt-lg row-px w-full">
      <AlertUnconfiguredService :is-configured="$page.props.is_configured_service" />

      <template
        v-if="
          !$page.props.is_service_active.twitter &&
          $page.props.enterprise_config.workspace_twitter_service
        "
      >
        <div class="mb-md">
          <Alert variant="warning" :closeable="false">
            <p>{{ $t('service.not_configured_service', { service: 'X' }) }}</p>
          </Alert>

          <Flex>
            <a
              :href="`/${$page.props.mixpost.core_path}/${workspaceCtx.id}/workspace-services?redirect-to-accounts=true`"
              class="mt-md block"
            >
              <PrimaryButton>{{ $t('service.configure_services') }}</PrimaryButton>
            </a>
          </Flex>
        </div>
      </template>

      <div class="w-full grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
        <button @click="addAccountModal = true">
          <GridAddElement>
            {{ $t('account.add_account') }}
          </GridAddElement>
        </button>
        <template v-for="account in $page.props.accounts" :key="account.id">
          <Panel class="relative">
            <div class="flex flex-col justify-center relative">
              <Account
                size="lg"
                :img-url="account.image"
                :provider="account.provider"
                :name="account.name"
                :active="true"
              />

              <div v-if="!account.authorized" class="absolute top-0 left-0">
                <div
                  v-tooltip="$t('account.unauthorized')"
                  class="w-md h-md bg-red-500 rounded-full"
                ></div>
              </div>

              <div class="mt-sm font-medium text-center break-words">
                {{ account.name }}
              </div>
              <div class="mt-1 text-center text-stone-800">
                {{ $t('account.added') }}
                {{ account.created_at }}
              </div>
            </div>

            <div class="absolute top-0 right-0 mt-sm mr-sm">
              <Dropdown>
                <template #trigger>
                  <PureButton>
                    <EllipsisVerticalIcon />
                  </PureButton>
                </template>
                <template #content>
                  <DropdownItem
                    v-if="account.external_account_url"
                    as="a"
                    :href="account.external_account_url"
                    target="_blank"
                  >
                    <template #icon>
                      <ArrowTopRightOnSquare />
                    </template>
                    {{ $t('account.view_profile') }}
                  </DropdownItem>
                  <DropdownItem as="button" @click="updateAccount(account.uuid)">
                    <template #icon>
                      <RefreshIcon />
                    </template>
                    {{ $t('general.update') }}
                  </DropdownItem>
                  <DropdownItem as="button" @click="openSuffixForm(account)">
                    <template #icon>
                      <PencilSquare />
                    </template>
                    {{ $t('account.edit_suffix') }}
                  </DropdownItem>
                  <template v-if="$page.props.mixpost.features.api_access_tokens">
                    <MenuDelimiter />
                    <DropdownGroupTitle>{{ $t('system.usage_api') }}</DropdownGroupTitle>
                    <DropdownItem as="button" :copyable="true" :copy-text="account.id">
                      {{ $t('system.copy_id') }}
                    </DropdownItem>
                    <DropdownItem as="button" :copyable="true" :copy-text="account.uuid">
                      {{ $t('system.copy_uuid') }}
                    </DropdownItem>
                    <MenuDelimiter />
                  </template>
                  <DropdownItem as="button" @click="confirmationAccountDeletion = account.uuid">
                    <template #icon>
                      <TrashIcon class="text-red-500" />
                    </template>
                    {{ $t('general.delete') }}
                  </DropdownItem>
                </template>
              </Dropdown>
            </div>
          </Panel>
        </template>
      </div>
    </div>
  </div>

  <DialogModal
    :show="showSuffixForm"
    max-width="sm"
    :closeable="true"
    :scrollable-body="true"
    @close="closeSuffixForm"
  >
    <template #header>
      {{ $t('account.edit_account_suffix') }}
    </template>

    <template #body>
      <div class="mt-xs">
        <Input v-model="suffixForm.name" type="text" :placeholder="$t('account.enter_suffix')" />
      </div>
    </template>

    <template #footer>
      <SecondaryButton class="mr-xs" @click="closeSuffixForm"
        >{{ $t('general.close') }}
      </SecondaryButton>
      <PrimaryButton
        :is-loading="suffixForm.processing"
        :disabled="suffixForm.processing"
        @click="updateSuffix"
      >
        {{ $t('general.save') }}
      </PrimaryButton>
    </template>
  </DialogModal>

  <ConfirmationModal
    :show="confirmationAccountDeletion !== null"
    variant="danger"
    @close="closeConfirmationAccountDeletion"
  >
    <template #header>{{ $t('account.delete_account') }}</template>
    <template #body>
      {{ $t('account.confirm_delete_account') }}
    </template>
    <template #footer>
      <SecondaryButton
        :disabled="accountIsDeleting"
        class="mr-xs"
        @click="closeConfirmationAccountDeletion"
      >
        {{ $t('general.cancel') }}
      </SecondaryButton>
      <DangerButton
        :is-loading="accountIsDeleting"
        :disabled="accountIsDeleting"
        @click="deleteAccount"
      >
        {{ $t('general.delete') }}
      </DangerButton>
    </template>
  </ConfirmationModal>

  <Modal :show="addAccountModal" :closeable="true" @close="addAccountModal = false">
    <div class="flex flex-col">
      <AddFacebookPage v-if="$page.props.is_service_active.facebook" />
      <AddInstagramAccount v-if="$page.props.is_service_active.facebook" />
      <AddThreadsAccount v-if="$page.props.is_service_active.threads" />
      <AddMastodonAccount />
      <AddBlueskyAccount v-if="$page.props.is_service_active.bluesky" />
      <AddYoutubeAccount v-if="$page.props.is_service_active.google" />
      <AddGBPAccount v-if="$page.props.is_service_active.google" />
      <AddPinterestAccount v-if="$page.props.is_service_active.pinterest" />
      <AddLinkedinProfile v-if="$page.props.is_service_active.linkedin" />
      <AddLinkedinPage
        v-if="
          $page.props.is_service_active.linkedin && $page.props.additionally.linkedin_supports_pages
        "
      />
      <AddTikTokAccount v-if="$page.props.is_service_active.tiktok" />
      <AddTwitterAccount v-if="$page.props.is_service_active.twitter" />
    </div>
  </Modal>
</template>
