<script setup>
import Input from '../Form/Input.vue'
import Textarea from '../Form/Textarea.vue'
import ReadDocHelp from '../Util/ReadDocHelp.vue'
import Select from '../Form/Select.vue'
import VerticalGroup from '../Layout/VerticalGroup.vue'
import InputHidden from '../Form/InputHidden.vue'
import Label from '../Form/Label.vue'

defineProps({
  form: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div>
    <ReadDocHelp :href="`${$page.props.mixpost.docs_link}/services/payment/paddle`" />

    <VerticalGroup class="mt-lg">
      <template #title><label for="vendor_id">Vendor ID</label></template>
      <Input id="vendor_id" v-model="form.credentials.vendor_id" type="text" autocomplete="off" />
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title><label for="vendor_auth_code">Vendor Auth Code</label></template>
      <InputHidden
        id="vendor_auth_code"
        v-model="form.credentials.vendor_auth_code"
        autocomplete="off"
      />
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title><Label for="public_key">Public Key</Label></template>
      <Textarea id="public_key" v-model="form.credentials.public_key" autocomplete="off" />
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title><Label for="sandbox">Sandbox</Label></template>
      <Select id="sandbox" v-model="form.options.sandbox">
        <option :value="false">{{ $t('general.no') }}</option>
        <option :value="true">{{ $t('general.yes') }}</option>
      </Select>
    </VerticalGroup>
  </div>
</template>
