<script setup>
import Input from '../Form/Input.vue'
import ReadDocHelp from '../Util/ReadDocHelp.vue'
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
    <ReadDocHelp :href="`${$page.props.mixpost.docs_link}/services/payment/stripe`" />

    <VerticalGroup class="mt-lg">
      <template #title><Label for="key">Publishable key</Label></template>
      <Input id="key" v-model="form.credentials.key" type="text" autocomplete="off" />
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title><Label for="secret">Secret key</Label></template>
      <InputHidden id="secret" v-model="form.credentials.secret" autocomplete="off" />
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title><Label for="webhook_secret">Webhook Signing secret</Label></template>
      <InputHidden
        id="webhook_secret"
        v-model="form.credentials.webhook_secret"
        autocomplete="off"
      />
    </VerticalGroup>
  </div>
</template>
