<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import useNotifications from '../../Composables/useNotifications'
import Checkbox from '../Form/Checkbox.vue'
import Tab from '../Navigation/Tab.vue'
import Input from '../Form/Input.vue'
import SecondaryButton from '../Button/SecondaryButton.vue'
import Select from '../Form/Select.vue'
import DarkButtonLink from '../Button/DarkButtonLink.vue'
import Flex from '../Layout/Flex.vue'
import PureButton from '../Button/PureButton.vue'
import Textarea from '../Form/Textarea.vue'
import Alert from '../Util/Alert.vue'
import PrimaryButton from '../Button/PrimaryButton.vue'
import WarningButton from '../Button/WarningButton.vue'
import Cog from '../../Icons/Cog.vue'
import Label from '../Form/Label.vue'
import Radio from '../Form/Radio.vue'
import Switch from '../Form/Switch.vue'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import '@css/overrideFlatPickr.css'

defineProps({
  configs: {
    required: true,
    type: Object
  }
})

const { t: $t } = useI18n()

const { notify } = useNotifications()

const showNotification = () => {
  notify('success', $t('theme.example_notification'))
}

const confirmation = inject('confirmation')
const showModal = () => {
  confirmation()
    .title($t('theme.example_title_modal'))
    .description($t('theme.example_description_modal'))
    .btnConfirmName($t('general.confirm'))
    .onConfirm(dialog => {
      dialog.close()
      close()
    })
    .show()
}

const selectCheckbox = ref(true)
const selectRadio = ref(1)
const selectExample = ref('1')
const switchValue = ref(true)
const dateExample = ref(new Date())
const configDatePicker = {
  inline: true,
  dateFormat: 'Y-m-d',
  minDate: 'today',
  allowInput: false,
  monthSelectorType: 'static',
  yearSelectorType: 'static',
  static: true,
  locale: {
    firstDayOfWeek: 0
  },
  prevArrow:
    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>',
  nextArrow:
    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>'
}
</script>
<template>
  <Flex :wrap="true">
    <PrimaryButton>Button</PrimaryButton>
    <PrimaryButton disabled="disabled">{{ $t('general.disabled') }}</PrimaryButton>
    <SecondaryButton>Button</SecondaryButton>
    <DarkButtonLink href="#">Button</DarkButtonLink>
    <PureButton>
      <template #icon>
        <Cog />
      </template>
      Button
    </PureButton>
  </Flex>

  <Flex class="mt-lg">
    <Tab>Tab item</Tab>

    <Tab :active="true">{{ $t('theme.tab_item_active') }}</Tab>
  </Flex>

  <Input type="text" placeholder="Input" class="mt-lg" />
  <Textarea placeholder="Textarea" class="mt-lg" />

  <Select v-model="selectExample" class="mt-lg">
    <option value="1">Option 1</option>
    <option value="2">Option 2</option>
  </Select>

  <Flex class="mt-lg">
    <Label>
      <Checkbox v-model:checked="selectCheckbox" />
      Checkbox</Label
    >

    <Flex>
      <Label>
        <Radio v-model:checked="selectRadio" :value="0" />
        Example radio</Label
      >
      <Label>
        <Radio v-model:checked="selectRadio" :value="1" />
        Example radio</Label
      >
    </Flex>

    <Switch v-model="switchValue">
      <span class="mr-xs">
        <span v-if="switchValue">{{ $t('general.on') }}</span>
        <span v-if="!switchValue">{{ $t('general.off') }}</span>
      </span>
    </Switch>
  </Flex>

  <Flex :col="true" class="mt-lg">
    <Alert :closeable="false" class="w-full">Alert info</Alert>
    <Alert variant="success" :closeable="false" class="w-full">Alert success</Alert>
    <Alert variant="warning" :closeable="false" class="w-full">Alert warning</Alert>
    <Alert variant="error" :closeable="false" class="w-full">Alert error</Alert>
  </Flex>

  <div class="mt-lg">
    Notification
    <WarningButton @click="showNotification">
      {{ $t('theme.show_notification') }}
    </WarningButton>
  </div>

  <div class="mt-lg">
    Modal
    <WarningButton @click="showModal">
      {{ $t('theme.open_modal') }}
    </WarningButton>
  </div>

  <Flex class="mt-lg">
    <button v-tooltip="$t('theme.tooltip_content_here')" class="font-bold">
      {{ $t('theme.show_tooltip') }}
    </button>
  </Flex>

  <Flex class="mt-lg pickTime relative">
    <FlatPickr v-model="dateExample" :config="configDatePicker" />
  </Flex>
</template>
