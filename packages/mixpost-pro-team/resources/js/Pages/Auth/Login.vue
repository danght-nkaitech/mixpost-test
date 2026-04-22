<script setup>
import PrimaryButton from '@/Components/Button/PrimaryButton.vue'
import Error from '@/Components/Form/Error.vue'
import Input from '@/Components/Form/Input.vue'
import HorizontalGroup from '@/Components/Layout/HorizontalGroup.vue'
import Panel from '@/Components/Surface/Panel.vue'
import MinimalLayout from '@/Layouts/Minimal.vue'
import { initFirebase, isFirebaseReady, signInWithEmail, signInWithGoogle } from '@/Services/FirebaseAuth'
import { Trans } from '@/Services/Internationalization'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { inject, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import Checkbox from '../../Components/Form/Checkbox.vue'
import Label from '../../Components/Form/Label.vue'
import Flex from '../../Components/Layout/Flex.vue'
import useEnterpriseConsole from '../../Composables/useEnterpriseConsole'

defineOptions({ layout: MinimalLayout })

const I18n = useI18n()

const props = defineProps({
  locales: {
    type: Array,
    required: true
  },
  isForgotPasswordEnabled: {
    type: Boolean,
    required: true
  },
  firebaseConfig: {
    type: Object,
    default: null
  }
})

const routePrefix = inject('routePrefix')

const form = useForm({
  email: '',
  password: '',
  remember: true
})

const firebaseError = ref(null)
const googleLoading = ref(false)

onMounted(() => {
  if (props.firebaseConfig) {
    initFirebase(props.firebaseConfig)
  }
})

const getLocaleDirection = locale => {
  return props.locales.find(item => item.long === locale).direction || 'ltr'
}

const afterFirebaseLogin = () => {
  const userLocale = usePage().props.mixpost?.settings?.locale
  if (userLocale) {
    Trans.changeLocale(I18n, userLocale, getLocaleDirection(userLocale))
  }
}

const postFirebaseToken = (idToken) => {
  router.post(
    route('mixpost.firebase.login'),
    { id_token: idToken },
    {
      onSuccess: afterFirebaseLogin,
      onError: (errors) => {
        firebaseError.value = errors.email ?? Object.values(errors)[0] ?? 'Authentication failed.'
      }
    }
  )
}

const submit = async () => {
  firebaseError.value = null

  if (isFirebaseReady()) {
    try {
      const idToken = await signInWithEmail(form.email, form.password)
      postFirebaseToken(idToken)
      return
    } catch (err) {
      firebaseError.value = 'Invalid email or password. Please try again.'
    }
  }
}

const loginWithGoogle = async () => {
  if (!isFirebaseReady()) return
  firebaseError.value = null
  googleLoading.value = true
  try {
    const idToken = await signInWithGoogle()
    postFirebaseToken(idToken)
  } catch (err) {
    if (err.code !== 'auth/popup-closed-by-user') {
      firebaseError.value = 'Google sign-in failed. Please try again.'
    }
  } finally {
    googleLoading.value = false
  }
}

const { enterpriseConsole } = useEnterpriseConsole()
</script>
<template>
  <Head :title="$t('auth.sign_in')" />

  <div class="w-full sm:max-w-(--container-lg) mx-auto">
    <form @submit.prevent="submit">
      <Panel>
        <template #title>
          {{ $t('auth.login_account') }}
        </template>

        <template #description>
          {{ $t('auth.enter_details') }}
        </template>

        <Error v-for="(error, key) in form.errors" :key="key" :message="error" class="mb-xs" />
        <Error v-if="firebaseError" :message="firebaseError" class="mb-xs" />

        <HorizontalGroup>
          <template #title>
            <label for="email">{{ $t('general.email') }}</label>
          </template>

          <div class="w-full">
            <Input id="email" v-model="form.email" type="email" class="w-full" required />
          </div>
        </HorizontalGroup>

        <HorizontalGroup class="mt-md">
          <template #title>
            <label for="password">{{ $t('auth.password') }}</label>
          </template>

          <div class="w-full">
            <Input id="password" v-model="form.password" type="password" class="w-full" required />
          </div>
        </HorizontalGroup>

        <div class="mt-md">
          <Label>
            <Checkbox v-model:checked="form.remember" />
            {{ $t('auth.remember_me') }}
          </Label>
        </div>

        <Flex class="justify-between mt-lg">
          <PrimaryButton :disabled="form.processing" :is-loading="form.processing" type="submit">
            {{ $t('auth.login') }}
          </PrimaryButton>

          <template v-if="$page.props.isForgotPasswordEnabled">
            <Link :href="route(`${routePrefix}.password.request`)" class="link-primary"
              >{{ $t('auth.forgot_password') }}
            </Link>
          </template>
        </Flex>

        <template v-if="firebaseConfig && firebaseConfig.apiKey">
          <div class="relative my-lg flex items-center gap-3">
            <div class="flex-1 border-t border-gray-200" />
            <span class="text-sm text-gray-400">or</span>
            <div class="flex-1 border-t border-gray-200" />
          </div>

          <button
            type="button"
            :disabled="googleLoading"
            class="flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
            @click="loginWithGoogle"
          >
            <svg viewBox="0 0 24 24" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            {{ googleLoading ? 'Signing in...' : 'Sign in with Google' }}
          </button>
        </template>

        <template v-if="enterpriseConsole.registration_url">
          <div class="text-center mt-2xl">
            <p class="text-black">
              {{ $t('auth.dont_have_account') }}
              <a :href="enterpriseConsole.registration_url" class="link-primary"
                >{{ $t('auth.register_here') }}
              </a>
            </p>
          </div>
        </template>
      </Panel>
    </form>
  </div>
</template>
