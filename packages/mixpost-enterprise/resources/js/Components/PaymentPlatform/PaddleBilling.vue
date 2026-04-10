<script setup>
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
    <ReadDocHelp :href="`${$page.props.mixpost.docs_link}/services/payment/paddle-billing`" />

    <VerticalGroup class="mt-lg">
      <template #title><Label for="vendor_id">API Key</Label></template>
      <InputHidden id="vendor_id" v-model="form.credentials.api_key" autocomplete="off" />
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title><Label for="vendor_auth_code">Client Side Token</Label></template>
      <InputHidden
        id="vendor_auth_code"
        v-model="form.credentials.client_side_token"
        autocomplete="off"
      />
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title><Label for="webhook_secret">Webhook secret key</Label></template>
      <InputHidden
        id="webhook_secret"
        v-model="form.credentials.webhook_secret"
        autocomplete="off"
      />
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
