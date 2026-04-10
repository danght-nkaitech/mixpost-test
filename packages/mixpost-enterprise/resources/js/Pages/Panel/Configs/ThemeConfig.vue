<script setup>
import { computed, inject } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { cloneDeep } from 'lodash'
import Panel from '../../../Components/Surface/Panel.vue'
import PrimaryButton from '../../../Components/Button/PrimaryButton.vue'
import UploadButton from '../../../Components/Media/UploadButton.vue'
import SecondaryButton from '../../../Components/Button/SecondaryButton.vue'
import Error from '../../../Components/Form/Error.vue'
import Label from '../../../Components/Form/Label.vue'
import HorizontalGroup from '../../../Components/Layout/HorizontalGroup.vue'
import DangerButton from '../../../Components/Button/DangerButton.vue'
import X from '../../../Icons/X.vue'
import Select from '../../../Components/Form/Select.vue'
import ColoredBox from '../../../Components/Util/ColoredBox.vue'
import TriggerColorPicker from '../../../Components/Util/TriggerColorPicker.vue'
import PreviewComponents from '../../../Components/Theme/PreviewComponents.vue'
import Flex from '../../../Components/Layout/Flex.vue'
import Settings from '../../../Layouts/Child/Settings.vue'
import { convertLaravelErrorsToString } from '@/helpers.js'
import useNotifications from '@/Composables/useNotifications.js'

const routePrefix = inject('routePrefix')

const props = defineProps({
  configs: {
    required: true,
    type: Object
  },
  primaryColors: {
    required: true,
    type: Object
  }
})

const form = useForm(cloneDeep(props.configs))

const { notify } = useNotifications()

const save = () => {
  form.put(route(`${routePrefix}.configs.theme.update`), {
    onError: error => {
      notify('error', convertLaravelErrorsToString(error))
    }
  })
}

const isCustom = computed(() => {
  return form.app_color === 'custom'
})
</script>
<template>
  <Head :title="$t('panel.customization')" />

  <Settings>
    <Flex align="start" gap="gap-2xl">
      <div class="w-full md:w-3/5">
        <form method="post" @submit.prevent="save">
          <Panel>
            <template #title>{{ $t('theme.logo_favicon') }}</template>
            <template #description>{{ $t('theme.rebrand_company') }}</template>

            <HorizontalGroup>
              <template #title>
                <div class="flex">
                  <UploadButton
                    :with-preload="true"
                    class="mr-xs"
                    @upload="
                      e => {
                        if (e.is_local_driver) {
                          form.logo_url = `/storage/${e.path}`
                          return
                        }

                        form.logo_url = e.url
                      }
                    "
                  >
                    <SecondaryButton id="logo">{{ $t('theme.upload_logo') }} </SecondaryButton>
                  </UploadButton>

                  <template v-if="form.logo_url">
                    <DangerButton
                      v-tooltip="$t('theme.remove_logo')"
                      size="sm"
                      @click="form.logo_url = ''"
                    >
                      <X />
                    </DangerButton>
                  </template>
                </div>
              </template>

              <img
                v-if="form.logo_url"
                :src="form.logo_url"
                class="w-auto max-h-20 mt-xs"
                alt="Logo"
              />

              <template #footer>
                <Error :message="form.errors.logo_url" />
              </template>
            </HorizontalGroup>

            <HorizontalGroup class="mt-lg">
              <template #title>
                <div class="flex">
                  <UploadButton
                    :with-preload="true"
                    class="mr-xs"
                    @upload="
                      e => {
                        if (e.is_local_driver) {
                          form.favicon_url = `/storage/${e.path}`
                          return
                        }

                        form.favicon_url = e.url
                      }
                    "
                  >
                    <SecondaryButton id="logo"
                      >{{ $t('theme.upload_favicon') }} (16 x 16 px)
                    </SecondaryButton>
                  </UploadButton>

                  <template v-if="form.favicon_url">
                    <DangerButton
                      v-tooltip="$t('theme.remove_favicon')"
                      size="sm"
                      @click="form.favicon_url = ''"
                    >
                      <X />
                    </DangerButton>
                  </template>
                </div>
              </template>

              <img
                v-if="form.favicon_url"
                :src="form.favicon_url"
                class="w-auto max-h-20 mt-xs"
                alt="Logo"
              />

              <template #footer>
                <Error :message="form.errors.favicon_url" />
              </template>
            </HorizontalGroup>

            <HorizontalGroup class="mt-lg">
              <template #title>
                <div class="flex">
                  <UploadButton
                    :with-preload="true"
                    class="mr-xs"
                    @upload="
                      e => {
                        if (e.is_local_driver) {
                          form.favicon_chrome_small_url = `/storage/${e.path}`
                          return
                        }

                        form.favicon_chrome_small_url = e.url
                      }
                    "
                  >
                    <SecondaryButton id="logo"
                      >{{ $t('theme.upload_favicon') }} (192 x 192 px) (png)
                    </SecondaryButton>
                  </UploadButton>

                  <template v-if="form.favicon_chrome_small_url">
                    <DangerButton
                      v-tooltip="$t('theme.remove_favicon')"
                      size="sm"
                      @click="form.favicon_chrome_small_url = ''"
                    >
                      <X />
                    </DangerButton>
                  </template>
                </div>
              </template>

              <img
                v-if="form.favicon_chrome_small_url"
                :src="form.favicon_chrome_small_url"
                class="w-auto max-h-20 mt-xs"
                alt="Logo"
              />

              <template #footer>
                <Error :message="form.errors.favicon_chrome_small_url" />
              </template>
            </HorizontalGroup>

            <HorizontalGroup class="mt-lg">
              <template #title>
                <div class="flex">
                  <UploadButton
                    :with-preload="true"
                    class="mr-xs"
                    @upload="
                      e => {
                        if (e.is_local_driver) {
                          form.favicon_chrome_medium_url = `/storage/${e.path}`
                          return
                        }

                        form.favicon_chrome_medium_url = e.url
                      }
                    "
                  >
                    <SecondaryButton id="logo"
                      >{{ $t('theme.upload_favicon') }} (512 x 512 px) (png)
                    </SecondaryButton>
                  </UploadButton>

                  <template v-if="form.favicon_chrome_medium_url">
                    <DangerButton
                      v-tooltip="$t('theme.remove_favicon')"
                      size="sm"
                      @click="form.favicon_chrome_medium_url = ''"
                    >
                      <X />
                    </DangerButton>
                  </template>
                </div>
              </template>

              <img
                v-if="form.favicon_chrome_medium_url"
                :src="form.favicon_chrome_medium_url"
                class="w-auto max-h-20 mt-xs"
                alt="Logo"
              />

              <template #footer>
                <Error :message="form.errors.favicon_chrome_medium_url" />
              </template>
            </HorizontalGroup>
          </Panel>

          <Panel class="mt-lg">
            <template #title>{{ $t('theme.theme_color') }}</template>
            <template #description>{{ $t('theme.rebrand_company') }}</template>

            <HorizontalGroup class="mt-lg">
              <template #title>
                <Label>{{ $t('theme.primary_color') }}</Label>
              </template>

              <Select v-model="form.app_color">
                <option v-for="color in primaryColors" :key="color.key" :value="color.key">
                  {{ color.name }}
                </option>
                <option value="custom">{{ $t('theme.custom') }}</option>
              </Select>
            </HorizontalGroup>

            <template v-if="isCustom">
              <HorizontalGroup class="mt-lg">
                <template #title>
                  <Label>{{ $t('theme.primary_colors') }}</Label>
                </template>

                <div>
                  <Flex align="between" :responsive="false">
                    <template v-for="(value, key) in form.app_custom_colors.primary" :key="key">
                      <TriggerColorPicker
                        :color="value"
                        @update="form.app_custom_colors.primary[key] = $event"
                      >
                        <ColoredBox :color="value">
                          {{ key }}
                        </ColoredBox>
                      </TriggerColorPicker>
                    </template>
                  </Flex>

                  <div class="mt-xs">
                    <a
                      href="https://uicolors.app/browse/tailwind-colors"
                      target="_blank"
                      class="block link"
                      >- {{ $t('theme.inspire_here') }}</a
                    >
                    <a href="https://uicolors.app/create" target="_blank" class="block link"
                      >- {{ $t('theme.generate_here') }}</a
                    >
                  </div>
                </div>
              </HorizontalGroup>

              <HorizontalGroup class="mt-lg">
                <template #title>
                  <Label>{{ $t('theme.primary_button') }}</Label>
                </template>

                <TriggerColorPicker
                  :color="form.app_custom_colors.primary_context"
                  @update="form.app_custom_colors.primary_context = $event"
                >
                  <ColoredBox :color="form.app_custom_colors.primary_context" />
                </TriggerColorPicker>
              </HorizontalGroup>
            </template>

            <hr class="my-lg" />

            <HorizontalGroup class="mt-lg">
              <template #title>
                <Label> {{ $t('theme.alert_background') }}</Label>
              </template>

              <TriggerColorPicker
                :color="form.app_custom_colors.alert"
                @update="form.app_custom_colors.alert = $event"
              >
                <ColoredBox :color="form.app_custom_colors.alert" />
              </TriggerColorPicker>
            </HorizontalGroup>

            <HorizontalGroup class="mt-lg">
              <template #title>
                <Label>{{ $t('theme.alert_text') }}</Label>
              </template>

              <TriggerColorPicker
                :color="form.app_custom_colors.alert_context"
                @update="form.app_custom_colors.alert_context = $event"
              >
                <ColoredBox :color="form.app_custom_colors.alert_context" />
              </TriggerColorPicker>
            </HorizontalGroup>
          </Panel>

          <div class="flex items-center mt-lg">
            <PrimaryButton type="submit" :disabled="form.processing" :is-loading="form.processing">
              {{ $t('general.save') }}
            </PrimaryButton>
          </div>
        </form>
      </div>

      <div class="w-full md:w-2/5">
        <Panel>
          <template #title>{{ $t('general.preview') }}</template>

          <PreviewComponents :configs="form" />
        </Panel>
      </div>
    </Flex>
  </Settings>
</template>
