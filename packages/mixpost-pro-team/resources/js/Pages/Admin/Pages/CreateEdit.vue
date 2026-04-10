<script setup>
import { computed, inject, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { cloneDeep } from 'lodash'
import AdminLayout from '@/Layouts/Admin.vue'
import usePageMode from '@/Composables/usePageMode'
import useNotifications from '@/Composables/useNotifications'
import PageHeader from '@/Components/DataDisplay/PageHeader.vue'
import Panel from '@/Components/Surface/Panel.vue'
import Error from '@/Components/Form/Error.vue'
import Input from '@/Components/Form/Input.vue'
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import DangerButton from '../../../Components/Button/DangerButton.vue'
import Trash from '../../../Icons/Trash.vue'
import ArrowTopRightOnSquare from '../../../Icons/ArrowTopRightOnSquare.vue'
import VerticalGroup from '../../../Components/Layout/VerticalGroup.vue'
import Select from '../../../Components/Form/Select.vue'
import LabelSuffix from '../../../Components/Form/LabelSuffix.vue'
import Textarea from '../../../Components/Form/Textarea.vue'
import PageBlocks from '../../../Components/Page/PageBlocks.vue'

defineOptions({ layout: AdminLayout })

const { t: $t } = useI18n()

const props = defineProps({
  mode: {
    type: String,
    default: 'create'
  },
  page: {
    type: [Object, null]
  }
})

const { notify } = useNotifications()

const routePrefix = inject('routePrefix')
const confirmation = inject('confirmation')

const pageTitle = computed(() => {
  if (isCreate.value) {
    return $t('page.create_page')
  }

  return $t('page.edit_page')
})

const { isCreate, isEdit } = usePageMode()

const form = useForm(
  isEdit.value
    ? cloneDeep(props.page)
    : {
        name: '',
        meta_title: '',
        meta_description: '',
        slug: '',
        layout: 'default',
        status: 1,
        blocks: []
      }
)

const watchBlocks = () => {
  watch(
    () => props.page.blocks,
    val => {
      form.blocks = val
    }
  )
}

if (isEdit.value) {
  watchBlocks()
}

const updateSlug = () => {
  form.slug = props.page.slug
}

const store = transformedForm => {
  transformedForm.post(route(`${routePrefix}.pages.store`), {
    onSuccess: () => {
      updateSlug()
      notify('success', $t('page.page_created'))
    }
  })
}

const update = transformedForm => {
  transformedForm.put(route(`${routePrefix}.pages.update`, { page: props.page.uuid }), {
    preserveScroll: true,
    onSuccess: () => {
      updateSlug()
      notify('success', $t('page.page_updated'))
    }
  })
}

const deletePage = () => {
  confirmation()
    .title($t('page.delete_page'))
    .description($t('page.confirm_delete_page'))
    .destructive()
    .onConfirm(dialog => {
      dialog.isLoading(true)

      router.delete(route(`${routePrefix}.pages.delete`, { page: props.page.uuid }), {
        preserveScroll: true,
        onSuccess() {
          notify('success', $t('page.page_deleted'))
        },
        onFinish() {
          dialog.reset()
        }
      })
    })
    .show()
}

const submit = () => {
  const transformedForm = form.transform(data => {
    return {
      ...data,
      blocks: data.blocks.map(block => block.id)
    }
  })

  if (isCreate.value) {
    store(transformedForm)
  }

  if (isEdit.value) {
    update(transformedForm)
  }
}
</script>
<template>
  <Head :title="pageTitle" />

  <div class="row-py w-full mx-auto">
    <PageHeader :title="pageTitle">
      <template #description>{{ $t('page.page_desc') }}</template>
      <div v-if="isEdit" class="flex">
        <a
          :href="route(`${routePrefix}.pages.show`, { slug: page.url_path })"
          target="_blank"
          class="mr-xs rtl:mr-0 rtl:ml-xs"
        >
          <SecondaryButton size="sm">
            <template #icon>
              <ArrowTopRightOnSquare />
            </template>
            {{ $t('general.open') }}
          </SecondaryButton>
        </a>

        <DangerButton size="sm" @click="deletePage">
          <template #icon>
            <Trash />
          </template>
        </DangerButton>
      </div>
    </PageHeader>

    <div class="row-px">
      <form method="post" @submit.prevent="submit">
        <div class="flex gap-lg">
          <div class="w-2/3">
            <Panel>
              <template #title>{{ $t('general.details') }}</template>

              <VerticalGroup>
                <template #title>
                  <label for="name"
                    >{{ $t('general.name') }}
                    <LabelSuffix :danger="true">*</LabelSuffix>
                  </label>
                </template>

                <Input
                  id="name"
                  v-model="form.name"
                  :error="form.errors.name !== undefined"
                  type="text"
                  class="w-full"
                  :autofocus="isCreate"
                  required
                />

                <template #footer>
                  <Error :message="form.errors.name" />
                </template>
              </VerticalGroup>

              <VerticalGroup class="mt-lg">
                <template #title>
                  <label for="meta_title">{{ $t('page.meta_title') }}</label>
                </template>

                <Input
                  id="meta_title"
                  v-model="form.meta_title"
                  :error="form.errors.meta_title !== undefined"
                  type="text"
                  class="w-full"
                />

                <template #footer>
                  <Error :message="form.errors.meta_title" />
                </template>
              </VerticalGroup>

              <VerticalGroup class="mt-lg">
                <template #title>
                  <label for="meta_description">{{ $t('page.meta_description') }}</label>
                </template>

                <Textarea
                  id="meta_description"
                  v-model="form.meta_description"
                  :error="form.errors.meta_description !== undefined"
                  class="w-full"
                />

                <template #footer>
                  <Error :message="form.errors.meta_description" />
                </template>
              </VerticalGroup>
            </Panel>
            <Panel class="mt-lg">
              <template #title>{{ $t('general.blocks') }}</template>

              <PageBlocks :page="form" />
            </Panel>
          </div>
          <div class="w-1/3">
            <Panel>
              <VerticalGroup>
                <template #title>
                  <label for="slug"
                    >{{ $t('page.url_path') }}
                    <LabelSuffix :danger="true">*</LabelSuffix>
                    <span
                      class="text-sm text-gray-400 inline-block ml-xs font-normal"
                      v-html="$t('page.type_home')"
                    />
                  </label>
                </template>

                <Input
                  id="slug"
                  v-model="form.slug"
                  :error="form.errors.slug !== undefined"
                  type="text"
                  class="w-full"
                  autocomplete="off"
                  required
                />

                <template #footer>
                  <Error :message="form.errors.slug" />
                </template>
              </VerticalGroup>

              <VerticalGroup class="mt-lg">
                <template #title>
                  <label for="layout">{{ $t('page.container_width') }}</label>
                </template>

                <Select id="layout" v-model="form.layout" :error="form.errors.layout !== undefined">
                  <option value="default">{{ $t('general.default') }}</option>
                  <option value="medium">{{ $t('page.medium') }}</option>
                  <option value="small">{{ $t('page.small') }}</option>
                </Select>

                <template #footer>
                  <Error :message="form.errors.layout" />
                </template>
              </VerticalGroup>

              <VerticalGroup class="mt-lg">
                <template #title>
                  <label for="status">{{ $t('general.status') }}</label>
                </template>

                <Select id="status" v-model="form.status" :error="form.errors.status !== undefined">
                  <option :value="1">{{ $t('general.enabled') }}</option>
                  <option :value="0">{{ $t('general.disabled') }}</option>
                </Select>

                <template #footer>
                  <Error :message="form.errors.status" />
                </template>
              </VerticalGroup>

              <div class="flex items-center mt-lg">
                <PrimaryButton
                  type="submit"
                  :disabled="form.processing"
                  :is-loading="form.processing"
                >
                  {{ isCreate ? $t('general.create') : $t('general.update') }}
                </PrimaryButton>
              </div>
            </Panel>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
