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
    <ReadDocHelp :href="`${$page.props.mixpost.docs_link}/services/payment/paystack`" />

    <VerticalGroup class="mt-lg">
      <template #title><Label for="secret_key">Secret Key</Label></template>
      <InputHidden id="secret_key" v-model="form.credentials.secret_key" autocomplete="off" />
    </VerticalGroup>

    <VerticalGroup class="mt-lg">
      <template #title><Label for="public_key">Public Key</Label></template>
      <Input id="public_key" v-model="form.credentials.public_key" type="text" autocomplete="off" />
    </VerticalGroup>
  </div>
</template>
