<script setup>
import { inject } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import useTemplate from '../../../Composables/useTemplate'
import PageHeader from '../../../Components/DataDisplay/PageHeader.vue'
import Panel from '../../../Components/Surface/Panel.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import Trash from '../../../Icons/Trash.vue'
import PencilSquare from '../../../Icons/PencilSquare.vue'
import Dropdown from '../../../Components/Dropdown/Dropdown.vue'
import DropdownItem from '../../../Components/Dropdown/DropdownItem.vue'
import NoResult from '../../../Components/Util/NoResult.vue'
import PureButton from '../../../Components/Button/PureButton.vue'
import EllipsisVertical from '../../../Icons/EllipsisVertical.vue'
import Masonry from '../../../Components/Layout/Masonry.vue'
import TemplateItem from '../../../Components/TemplateManager/TemplateItem.vue'
import ListGroup from '../../../Components/DataDisplay/ListGroup.vue'
import ListItem from '../../../Components/DataDisplay/ListItem.vue'
import Plus from '../../../Icons/Plus.vue'

const workspaceCtx = inject('workspaceCtx')

defineProps({
  templates: {
    type: Array,
    required: true
  }
})

const { createPost, formatTemplateContent, deleteTemplate } = useTemplate()
</script>
<template>
  <Head :title="$t('template.templates')" />

  <div class="row-py">
    <PageHeader :title="$t('template.templates')">
      <template #description>{{ $t('template.templates_desc') }}</template>
    </PageHeader>

    <div class="w-full row-px">
      <Link :href="route('mixpost.templates.create', { workspace: workspaceCtx.id })">
        <PrimaryButton size="sm">
          <Plus class="mr-xs" />
          {{ $t('template.create_template') }}
        </PrimaryButton>
      </Link>

      <div class="relative mt-lg">
        <template v-if="!templates.length">
          <Panel>
            <NoResult>
              {{ $t('template.do_not_have_templates') }}
            </NoResult>
          </Panel>
        </template>

        <template v-else>
          <Masonry :items="templates" :columns="3">
            <template #default="{ item }">
              <ListGroup class="group">
                <ListItem :with-classes-for-last="false" class="p-lg! bg-white rounded-lg">
                  <TemplateItem :template="item">
                    <template #action>
                      <div class="flex justify-end">
                        <PrimaryButton
                          size="sm"
                          class="group-visible mr-xs"
                          @click="
                            () => {
                              createPost(formatTemplateContent(item.content))
                            }
                          "
                        >
                          {{ $t('general.use') }}
                        </PrimaryButton>

                        <Dropdown placement="bottom-end">
                          <template #trigger>
                            <PureButton class="mt-1">
                              <EllipsisVertical />
                            </PureButton>
                          </template>

                          <template #content>
                            <DropdownItem
                              link-as="a"
                              :href="
                                route('mixpost.templates.edit', {
                                  workspace: workspaceCtx.id,
                                  template: item.id
                                })
                              "
                            >
                              <PencilSquare class="w-5! h-5! mr-1" />
                              {{ $t('general.edit') }}
                            </DropdownItem>

                            <DropdownItem as="button" @click="deleteTemplate(item)">
                              <Trash class="w-5! h-5! mr-1 text-red-500" />
                              {{ $t('general.delete') }}
                            </DropdownItem>
                          </template>
                        </Dropdown>
                      </div>
                    </template>
                  </TemplateItem>
                </ListItem>
              </ListGroup>
            </template>
          </Masonry>
        </template>
      </div>
    </div>
  </div>
</template>
