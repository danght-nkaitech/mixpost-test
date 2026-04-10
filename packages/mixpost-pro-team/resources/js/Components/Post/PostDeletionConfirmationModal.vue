<script setup>
import Radio from '@/Components/Form/Radio.vue'
import SecondaryButton from '@/Components/Button/SecondaryButton.vue'
import DangerButton from '@/Components/Button/DangerButton.vue'
import ConfirmationModal from '@/Components/Modal/ConfirmationModal.vue'
import { computed, ref } from 'vue'
import usePostVersions from '@/Composables/usePostVersions.js'

const props = defineProps({
  posts: {
    type: Array
  },
  supportPostDeletion: {
    type: Object,
    default: () => ({})
  },
  deleteHandler: {
    type: Function,
    required: true
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const deleteMode = ref('app_only')
const isDeleting = ref(false)

/**
 * Determines whether deletion from social media should be disabled based on provider support.
 *
 * Returns true if ALL live posts are from providers that don't support deletion of any content type.
 * Returns false if ANY post is from a provider that supports deletion for its content type.
 *
 * This is used to conditionally disable the "app_and_social" and "social_only" deletion options
 * in the deletion modal, leaving only "app_only" available when social deletion isn't possible.
 *
 * @example
 * // Scenario 1: Mixed provider support
 * // Posts: 2 Facebook posts, 1 X post
 * // Facebook: no deletion support for any type
 * // X: supports tweet deletion
 * // Result: false (user can delete X post from social media)
 *
 * @example
 * // Scenario 2: No provider support
 * // Posts: 3 Facebook posts
 * // Facebook: no deletion support for any type (e.g., no deletion support for stories, reels, etc.)
 * // Result: true (user can only delete from app, not social media)
 *
 * @returns {boolean} true if social media deletion should be disabled
 */
const shouldDisableSocialDeletion = computed(() => {
  if (!postsLiveProviderTypes.value.size) return false

  return Array.from(postsLiveProviderTypes.value).every(([provider, { types }]) => {
    const support = props.supportPostDeletion[provider]

    if (typeof support === 'boolean') {
      return !support
    }

    return Array.from(types).every(type => !support?.[type])
  })
})

const { getAccountVersion, getOriginalVersion } = usePostVersions()

/**
 * Extracts provider types from posts based on a callback that determines whether to include the type.
 * This function iterates through the posts and their accounts, checking if each account has an external URL.
 * If it does, it retrieves the version of the account and checks if the type should be included using the provided callback.
 * If the type is included, it adds the provider and its types
 *
 * @param {Function} shouldIncludeTypeCallback - Callback function to determine if a type should be included.
 * @returns {Map} A map where keys are provider names and values are objects containing provider name and a set of types.
 */
const extractProviderTypes = shouldIncludeTypeCallback => {
  const resultMap = new Map()

  props.posts.forEach(post => {
    post.accounts
      .filter(account => account.external_url)
      .forEach(account => {
        const version =
          getAccountVersion(post.versions, account.id) || getOriginalVersion(post.versions)

        if (!version) return

        const type = version.options?.[account.provider]?.type
        const typesArray = Array.isArray(type) ? type : [type]

        if (!shouldIncludeTypeCallback(type, account)) return

        if (!resultMap.has(account.provider)) {
          resultMap.set(account.provider, {
            name: account.provider_name,
            types: new Set()
          })
        }

        const providerEntry = resultMap.get(account.provider)
        typesArray.forEach(type => providerEntry.types.add(type))
      })
  })

  return resultMap
}

const postsLiveProviderTypes = computed(() => {
  return extractProviderTypes(() => true)
})

const postsUnsupportedLiveProviderTypes = computed(() => {
  return extractProviderTypes((type, account) => {
    const support = props.supportPostDeletion[account.provider]

    if (typeof support === 'boolean' && support) return false
    if (support?.[type]) return false

    return type
  })
})
</script>
<template>
  <ConfirmationModal :show="show" variant="danger" max-width="lg" @close="emit('close')">
    <template #header>
      {{ $t('post.delete_posts', posts.length) }}
    </template>
    <template #body>
      <template v-if="posts.some(post => post.accounts.some(account => account.external_url))">
        <p>
          {{ $t('post.confirmation_delete_posts_from', posts.length) }}
        </p>
        <div class="my-sm">
          <label>
            <Radio
              :checked="deleteMode"
              value="app_only"
              @update:checked="val => (deleteMode = val)"
            />
            {{ $t('post.delete_post_from.app_only') }}
          </label>
        </div>
        <div class="my-sm">
          <label :class="{ 'text-gray-400': shouldDisableSocialDeletion }">
            <Radio
              :checked="deleteMode"
              value="app_and_social"
              :disabled="shouldDisableSocialDeletion"
              @update:checked="val => (deleteMode = val)"
            />
            {{ $t('post.delete_post_from.app_and_social') }}
          </label>
        </div>
        <div class="my-sm">
          <label :class="{ 'text-gray-400': shouldDisableSocialDeletion }">
            <Radio
              :checked="deleteMode"
              value="social_only"
              :disabled="shouldDisableSocialDeletion"
              @update:checked="val => (deleteMode = val)"
            />
            {{ $t('post.delete_post_from.social_only') }}
          </label>
        </div>
        <div
          v-if="
            (['app_and_social', 'social_only'].includes(deleteMode) ||
              shouldDisableSocialDeletion) &&
            postsUnsupportedLiveProviderTypes.size
          "
          class="bg-red-50 rounded-md px-lg py-1 mt-md"
        >
          <ul class="list-disc">
            <template
              v-for="[provider, { name, types }] in postsUnsupportedLiveProviderTypes"
              :key="provider"
            >
              <li class="text-red-500 text-sm py-1">
                {{
                  $t('post.does_not_support_deleting', {
                    provider: name,
                    types: Array.from(types)
                      .map(type => $t(`service.${provider}.${type}`))
                      .join(', ')
                  })
                }}.
              </li>
            </template>
          </ul>
        </div>
      </template>
      <template v-else>
        {{ $t('post.confirmation_delete_post') }}
      </template>
    </template>
    <template #footer>
      <SecondaryButton
        class="mr-xs rtl:mr-0 rtl:ml-xs"
        @click="
          () => {
            emit('close')
            deleteMode = 'app_only'
          }
        "
        >{{ $t('general.cancel') }}
      </SecondaryButton>
      <DangerButton
        :is-loading="isDeleting"
        :disabled="isDeleting"
        @click="
          () => {
            isDeleting = true
            deleteHandler({ deleteMode }).then(() => {
              isDeleting = false
              deleteMode = 'app_only'
            })
          }
        "
        >{{ $t('general.delete') }}
      </DangerButton>
    </template>
  </ConfirmationModal>
</template>
