<script setup>
import { cloneDeep } from 'lodash'
import { computed, defineAsyncComponent, inject, provide, ref } from 'vue'
import { Head, router, useForm, Link } from '@inertiajs/vue3'
import NProgress from 'nprogress'
import { useI18n } from 'vue-i18n'
import usePostVersions from '@/Composables/usePostVersions'
import useEditor from '@/Composables/useEditor'
import useNotifications from '@/Composables/useNotifications'
import useTemplate from '../../../Composables/useTemplate'
import PageHeader from '../../../Components/DataDisplay/PageHeader.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import Input from '../../../Components/Form/Input.vue'
import LabelSuffix from '../../../Components/Form/LabelSuffix.vue'
import VerticalGroup from '../../../Components/Layout/VerticalGroup.vue'
import VariableManager from '../../../Components/VariableManager/VariableManager.vue'
import HashtagManager from '../../../Components/HashtagManager/HashtagManager.vue'
import Editor from '../../../Components/Package/Editor.vue'
import PostMedia from '../../../Components/Post/PostMedia.vue'
import EmojiPicker from '../../../Components/Package/EmojiPicker.vue'
import PostAddMedia from '../../../Components/Post/PostAddMedia.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import Trash from '../../../Icons/Trash.vue'
import DangerButton from '../../../Components/Button/DangerButton.vue'
import Flex from '../../../Components/Layout/Flex.vue'
const AIAssist = defineAsyncComponent(() => import('@/Components/AI/Text/AIAssist.vue'))

const { t: $t } = useI18n()

const props = defineProps({
  template: {
    type: Object,
    default: null
  }
})

const template = props.template ? cloneDeep(props.template) : null

provide('postCtx', {})
const workspaceCtx = inject('workspaceCtx')

const { notify } = useNotifications()
const { versionContentObject } = usePostVersions()
const { insertEmoji, insertContent, replaceContent, focusEditor } = useEditor()
const { createPost, formatTemplateContent, deleteTemplate } = useTemplate()

const isLoading = ref(false)
const form = useForm({
  name: template ? template.name : '',
  content: template ? template.content : [versionContentObject()]
})

const title = computed(() => {
  if (props.template) {
    return $t('template.edit_template')
  }

  return $t('template.create_new_template')
})

const getFormattedData = () => {
  return {
    name: form.name,
    content: formatTemplateContent(form.content)
  }
}

const save = () => {
  if (!props.template) {
    store(getFormattedData())
  }

  if (props.template) {
    update(getFormattedData())
  }
}

const store = (data, redirectToCreateRoute = false) => {
  NProgress.start()
  isLoading.value = true

  axios
    .post(route('mixpost.templates.api.store', { workspace: workspaceCtx.id }), data)
    .then(response => {
      notify('success', $t('template.template_created'))

      if (!redirectToCreateRoute) {
        router.get(
          route('mixpost.templates.edit', {
            workspace: workspaceCtx.id,
            template: response.data.id
          }),
          {},
          {
            preserveScroll: true
          }
        )

        return
      }

      router.get(route('mixpost.templates.create', { workspace: workspaceCtx.id }))
    })
    .finally(() => {
      NProgress.done()
      isLoading.value = false
    })
}

const update = data => {
  NProgress.start()
  isLoading.value = true

  axios
    .put(
      route('mixpost.templates.api.update', {
        workspace: workspaceCtx.id,
        template: props.template.id
      }),
      data
    )
    .then(() => {
      notify('success', $t('template.template_updated'))
    })
    .catch(error => {
      notify('error', error)
    })
    .finally(() => {
      NProgress.done()
      isLoading.value = false
    })
}
</script>
<template>
  <Head :title="title" />

  <div class="w-full mx-auto row-py">
    <PageHeader :title="title">
      <div class="flex">
        <template v-if="template">
          <PrimaryButton
            class="mr-xs"
            @click="
              () => {
                createPost(getFormattedData().content)
              }
            "
          >
            {{ $t('template.use_template') }}
          </PrimaryButton>
        </template>

        <Link
          v-if="template"
          :href="route('mixpost.templates.create', { workspace: workspaceCtx.id })"
        >
          <SecondaryButton class="mr-xs"> {{ $t('template.create_new') }}</SecondaryButton>
        </Link>

        <template v-if="template">
          <DangerButton
            size="sm"
            @click="
              () => {
                deleteTemplate(template)
              }
            "
          >
            <Trash />
          </DangerButton>
        </template>
      </div>
    </PageHeader>

    <div class="mt-lg row-px w-full">
      <form @submit.prevent="save">
        <Panel>
          <template #title>{{ $t('general.details') }}</template>
          <VerticalGroup class="mb-lg max-w-full!">
            <template #title>
              <label for="template_name"
                >{{ $t('template.template_name') }}
                <LabelSuffix :danger="true">*</LabelSuffix>
              </label>
            </template>
            <Input id="template_name" v-model="form.name" type="text" required />
          </VerticalGroup>

          <div class="form-field">
            <template v-for="(item, index) in form.content" :key="index">
              <Editor
                id="templateEditor"
                :value="item.body"
                :editable="true"
                @update="item.body = $event"
              >
                <template #default>
                  <PostMedia :media="item.media" @updated="item.media = $event" />

                  <Flex
                    :responsive="false"
                    class="relative justify-between border-t border-gray-200 pt-md mt-md"
                  >
                    <Flex :responsive="false">
                      <EmojiPicker
                        @selected="insertEmoji({ editorId: 'templateEditor', emoji: $event })"
                        @close="focusEditor({ editorId: 'templateEditor' })"
                      />

                      <PostAddMedia
                        @insert="
                          $event => {
                            item.media = [...item.media, ...$event.items]

                            if ($event.crediting) {
                              insertContent({ editorId: 'templateEditor', text: $event.crediting })
                            }
                          }
                        "
                      />

                      <HashtagManager
                        :edit-allowed="true"
                        @insert="insertContent({ editorId: 'templateEditor', text: $event })"
                      />

                      <VariableManager
                        :edit-allowed="true"
                        @insert="insertContent({ editorId: 'templateEditor', text: $event })"
                      />

                      <template v-if="$page.props.ai_is_ready_to_use">
                        <AIAssist
                          :text="item.body"
                          @insert="insertContent({ editorId: `templateEditor`, text: $event })"
                          @replace="replaceContent({ editorId: `templateEditor`, text: $event })"
                        />
                      </template>
                    </Flex>
                  </Flex>
                </template>
              </Editor>
            </template>
          </div>

          <Flex :responsive="false" class="items-center mt-lg">
            <PrimaryButton :disabled="isLoading" type="submit" class="mr-xs">
              {{ $t('template.save_template') }}
            </PrimaryButton>

            <template v-if="!template">
              <PrimaryButton
                :disabled="isLoading"
                type="button"
                @click="
                  () => {
                    store(getFormattedData(), true)
                  }
                "
              >
                {{ $t('template.save_create_new') }}
              </PrimaryButton>
            </template>
          </Flex>
        </Panel>
      </form>
    </div>
  </div>
</template>
