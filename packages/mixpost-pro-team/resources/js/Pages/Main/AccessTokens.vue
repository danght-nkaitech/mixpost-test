<script setup>
import { inject, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, router } from '@inertiajs/vue3'
import emitter from '@/Services/emitter'
import useSelectable from '../../Composables/useSelectable'
import MinimalLayout from '@/Layouts/Minimal.vue'
import PageHeader from '../../Components/DataDisplay/PageHeader.vue'
import Flex from '../../Components/Layout/Flex.vue'
import Trash from '../../Icons/Trash.vue'
import PureDangerButton from '../../Components/Button/PureDangerButton.vue'
import SelectableBar from '../../Components/DataDisplay/SelectableBar.vue'
import Panel from '../../Components/Surface/Panel.vue'
import Table from '../../Components/DataDisplay/Table.vue'
import TableRow from '../../Components/DataDisplay/TableRow.vue'
import TableCell from '../../Components/DataDisplay/TableCell.vue'
import Checkbox from '../../Components/Form/Checkbox.vue'
import NoResult from '../../Components/Util/NoResult.vue'
import TokenItem from '../../Components/UserToken/TokenItem.vue'
import CreateUserToken from '../../Components/UserToken/CreateUserToken.vue'
import Pagination from '../../Components/Navigation/Pagination.vue'
import useRouter from '../../Composables/useRouter'
import BackToDashboardButton from '../../Components/Helper/BackToDashboardButton.vue'

defineOptions({ layout: MinimalLayout })

const { t: $t } = useI18n()

const props = defineProps({
  tokens: {
    type: Object,
    required: true
  }
})

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const {
  selectedRecords,
  putPageRecords,
  toggleSelectRecordsOnPage,
  deselectRecord,
  deselectAllRecords
} = useSelectable()

const itemsId = () => {
  return props.tokens.data.map(item => item.id)
}

onMounted(() => {
  putPageRecords(itemsId())

  emitter.on('tokenDelete', id => {
    deselectRecord(id)
  })
})

watch(
  () => props.tokens.data,
  () => {
    putPageRecords(itemsId())
  }
)

const { onError } = useRouter()

const openDeleteTokensConfirmation = () => {
  confirmation()
    .title($t('access_token.delete_items'))
    .description($t('access_token.delete_items_confirm'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(route(`${routePrefix}.profile.accessTokens.deleteMultiple`), {
        data: {
          tokens: selectedRecords.value
        },
        preserveScroll: true,
        onSuccess() {
          dialog.reset()
          deselectAllRecords()
        },
        onError(errors) {
          onError(errors, () => {
            deleteTokensAfterConfirmed(dialog)
          })
        },
        onFinish() {
          dialog.isLoading(false)
        }
      })
    })
    .show()
}

const deleteTokensAfterConfirmed = dialog => {
  dialog.isLoading(true)

  router.delete(route(`${routePrefix}.profile.accessTokens.deleteMultiple`), {
    data: {
      tokens: selectedRecords.value
    },
    preserveScroll: true,
    onSuccess() {
      dialog.reset()
      emitter.emit('tokenDelete', props.item.id)
    },
    onError(errors) {
      onError(errors, () => {
        deleteTokensAfterConfirmed(dialog)
      })
    },
    onFinish() {
      dialog.isLoading(false)
    }
  })
}
</script>
<template>
  <Head :title="$t('access_token.access_tokens')" />

  <PageHeader :title="$t('access_token.access_tokens')" class="px-0!">
    <template #description>
      {{ $t('access_token.desc') }}
    </template>

    <BackToDashboardButton />
  </PageHeader>

  <div class="row-py w-full mx-auto pt-0!">
    <SelectableBar :count="selectedRecords.length" @close="deselectAllRecords">
      <PureDangerButton v-tooltip="$t('general.delete')" @click="openDeleteTokensConfirmation">
        <Trash />
      </PureDangerButton>
    </SelectableBar>

    <Flex>
      <CreateUserToken
        @added="
          router.get(
            route('mixpost.profile.accessTokens.index'),
            {},
            { only: ['tokens'], preserveState: true, preserveScroll: true }
          )
        "
      />
    </Flex>

    <Panel :with-padding="false" class="mt-lg">
      <Table>
        <template #head>
          <TableRow>
            <TableCell component="th" scope="col" class="w-10">
              <Checkbox
                v-model:checked="toggleSelectRecordsOnPage"
                :disabled="!tokens.data.length"
              />
            </TableCell>
            <TableCell component="th" scope="col">{{ $t('general.name') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('access_token.last_usage') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('access_token.expires_at') }}</TableCell>
            <TableCell component="th" scope="col">{{ $t('general.created_at') }}</TableCell>
            <TableCell component="th" scope="col" />
          </TableRow>
        </template>
        <template #body>
          <template v-for="item in tokens.data" :key="item.id">
            <TokenItem
              :item="item"
              @on-delete="
                () => {
                  deselectRecord(item.id)
                }
              "
            >
              <template #checkbox>
                <Checkbox v-model:checked="selectedRecords" :value="item.id" />
              </template>
            </TokenItem>
          </template>
        </template>
      </Table>

      <NoResult v-if="!tokens.data.length" class="p-md">{{
        $t('access_token.no_result')
      }}</NoResult>
    </Panel>

    <div v-if="tokens.meta.links.length > 3" class="mt-lg">
      <Pagination :meta="tokens.meta" :links="tokens.links" />
    </div>
  </div>
</template>
