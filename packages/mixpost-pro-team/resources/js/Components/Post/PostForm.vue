<script setup>
import { computed, defineAsyncComponent, inject, onMounted, ref, watch, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import { clone, cloneDeep, debounce, uniqBy } from 'lodash'
import { extractFirstURL, extractUrls } from '@/helpers.js'
import emitter from '@/Services/emitter'
import usePost from '@/Composables/usePost'
import usePostVersions from '@/Composables/usePostVersions'
import useEditor from '@/Composables/useEditor'
import Editor from '@/Components/Package/Editor.vue'
import EmojiPicker from '@/Components/Package/EmojiPicker.vue'
import Panel from '@/Components/Surface/Panel.vue'
import Account from '@/Components/Account/Account.vue'
import PostVersionsTab from '@/Components/Post/PostVersionsTab.vue'
import PostAddMedia from '@/Components/Post/PostAddMedia.vue'
import PostMedia from '@/Components/Post/PostMedia.vue'
import PostCharacterCount from '@/Components/Post/PostCharacterCount.vue'
import PostVersionOptions from '@/Components/Post/PostVersionOptions.vue'
import HashtagManager from '../HashtagManager/HashtagManager.vue'
import VariableManager from '../VariableManager/VariableManager.vue'
import TemplateManager from '../TemplateManager/TemplateManager.vue'
import usePostURLMeta from '../../Composables/usePostURLMeta'
import EditorButton from '../Button/EditorButton.vue'
import Plus from '../../Icons/Plus.vue'
import Flex from '../Layout/Flex.vue'
import EditorReadOnly from '../Package/EditorReadOnly.vue'
import X from '../../Icons/X.vue'
import ChevronUp from '../../Icons/ChevronUp.vue'
import ChevronDown from '../../Icons/ChevronDown.vue'
import PureButton from '../Button/PureButton.vue'
import MenuDelimiter from '../Sidebar/MenuDelimiter.vue'
import PostContentValidator from './PostContentValidator.vue'
import AlertText from '../Util/AlertText.vue'
import Badge from '../DataDisplay/Badge.vue'
import ChatBubbleBottomCenterText from '../../Icons/ChatBubbleBottomCenterText.vue'
import Link from '@/Icons/Link.vue'
import useNotifications from '@/Composables/useNotifications'

const workspaceCtx = inject('workspaceCtx')
const postCtx = inject('postCtx')

const AIAssist = defineAsyncComponent(() => import('@/Components/AI/Text/AIAssist.vue'))

const { t: $t } = useI18n()

const confirmation = inject('confirmation')

const { notify } = useNotifications()

const props = defineProps({
  form: {
    required: true,
    type: Object
  },
  accounts: {
    required: true,
    type: Array
  }
})

const { editAllowed } = usePost()
const { accountHasVersion } = usePostVersions()

/**
 * Account
 */
const selectAccount = account => {
  if (!editAllowed.value) {
    return
  }

  if (props.form.accounts.includes(account)) {
    props.form.accounts = props.form.accounts.filter(item => item !== account)
    return
  }

  const accounts = clone(props.form.accounts)
  accounts.push(account)

  props.form.accounts = accounts
}

const selectedAccounts = computed(() => {
  return props.accounts.filter(function (account) {
    return isAccountSelected(account)
  })
})

const providersWithDisabledSimultaneousPosting = computed(() => {
  return selectedAccounts.value
    .filter(account => {
      return !account.post_configs.simultaneous_posting
    })
    .map(account => {
      return account.provider
    })
})

const isAccountSelected = account => {
  return props.form.accounts.includes(account.id)
}

const isAccountUnselectable = account => {
  return (
    !isAccountSelected(account) &&
    providersWithDisabledSimultaneousPosting.value.includes(account.provider)
  )
}

const getAccount = (accounts, accountId) => {
  return accounts.find(account => account.id === accountId)
}

/**
 * Post content versions & Editor
 */
const {
  versionObject,
  getOriginalVersion,
  getAccountVersion,
  getIndexAccountVersion,
  getAccountsWithoutVersion,
  versionContentObject
} = usePostVersions()

const { setupURLMeta, setupURLMetaForAllVersions } = usePostURLMeta()

const activeVersion = ref(0)
const currentMaxCharLimit = ref(0)
const urlShortenerActivated = ref(false)

const resetActiveVersion = () => {
  activeVersion.value = 0
}

const content = computed(() => {
  return getAccountVersion(props.form.versions, activeVersion.value).content
})

const updateContent = (contentIndex, key, value) => {
  const versionIndex = getIndexAccountVersion(props.form.versions, activeVersion.value)

  props.form.versions[versionIndex].content[contentIndex][key] = value

  extractAndAssignUrlToContentItem(versionIndex, contentIndex)

  urlShortenerActivated.value && shortenUrls()
}

const shortenUrls = () => {
  const contentOpened = content.value.find(content => content.opened).body
  const urls = extractUrls(contentOpened, urlShortenerActivated.value)

  if (!urls.length) {
    return
  }

  axios
    .post(
      route('mixpost.url-shortener', {
        workspace: workspaceCtx.id,
        urls,
        url_shortener_active: urlShortenerActivated.value
      })
    )
    .then(response => {
      if (response.data.status === 'OK') {
        content.value.find(content => content.opened).body = swapUrls(
          contentOpened,
          response.data.urls,
          urlShortenerActivated.value
        )
      }
      if (response.data.status === 'ERROR' && response?.data?.message) {
        notify('error', response.data.message)
      }
    })
}

const swapUrls = (htmlString, urlsData, urlShortenerActive) => {
  const searchFor = urlShortenerActive ? 'original_url' : 'short_url'
  const replaceWith = urlShortenerActive ? 'short_url' : 'original_url'

  const parser = new DOMParser()
  const doc = parser.parseFromString(htmlString, 'text/html')

  const links = doc.querySelectorAll('a')

  links.forEach(link => {
    urlsData.forEach(urlData => {
      if (link.textContent.trim() === urlData[searchFor]) {
        link.href = urlData[replaceWith]
        link.textContent = urlData[replaceWith]

        urlShortenerActive
          ? link.classList.add('non_editable')
          : link.classList.remove('non_editable')
      }
    })
  })

  return doc.body.innerHTML
}

const extractAndAssignUrlToContentItem = debounce((versionIndex, contentIndex) => {
  const oldUrl = props.form.versions[versionIndex].content[contentIndex]['url']
  let newUrl = ''

  if (props.form.versions[versionIndex].content[contentIndex]['media'].length) {
    props.form.versions[versionIndex].content[contentIndex]['url'] = newUrl
  } else {
    newUrl = extractFirstURL(props.form.versions[versionIndex].content[contentIndex]['body'])

    props.form.versions[versionIndex].content[contentIndex]['url'] = newUrl
  }

  if (oldUrl !== newUrl) {
    setupURLMeta(oldUrl, newUrl)
  }
}, 300)

const addVersion = accountId => {
  const account = getAccount(props.accounts, accountId)

  const newVersion = versionObject(accountId)

  // Copy content from the default version to the new version
  const originalVersion = getOriginalVersion(props.form.versions)

  // Get the original version content based on the content type of the account
  newVersion.content = (() => {
    switch (account.content_type) {
      case 'comments':
        return cloneDeep(originalVersion.content).slice(0, 2)
      case 'thread':
        return cloneDeep(originalVersion.content)
      default:
        return cloneDeep(originalVersion.content).slice(0, 1)
    }
  })()

  // Copy options from the default version to the new version
  newVersion.options = cloneDeep(originalVersion.options)

  // Add the new version to the version array
  props.form.versions.push(newVersion)

  // Set added version as an active version
  activeVersion.value = accountId
}

const removeVersion = accountId => {
  if (!accountId) {
    return
  }

  const versionIndex = getIndexAccountVersion(props.form.versions, accountId)

  if (versionIndex < 0) {
    return
  }

  props.form.versions.splice(versionIndex, 1)
}

const setupVersions = () => {
  // If an account was deselected, we make sure to change the active version to the default version
  const isAccountSelected = props.form.accounts.includes(activeVersion.value)

  if (!isAccountSelected) {
    resetActiveVersion()
  }

  // If is only one account selected and if is original active version, we switch active version for that account.
  if (props.form.accounts.length === 1 && activeVersion.value === 0) {
    const firstAccountId = props.form.accounts[0]

    if (firstAccountId !== 0 && getAccountVersion(props.form.versions, firstAccountId)) {
      activeVersion.value = firstAccountId
    }
  }
}

const contentItemsCount = computed(() => {
  return content.value.length
})

const getActiveAccounts = () => {
  return activeVersion.value === 0
    ? selectedAccounts.value.filter(account => {
        return !accountHasVersion(props.form.versions, account.id)
      })
    : [getAccount(selectedAccounts.value, activeVersion.value)]
}

// Get the providers that have a single content type from selected accounts
const singleContentProviders = computed(() => {
  return getActiveAccounts().reduce((providers, account) => {
    if (account.content_type === 'single') {
      providers.push(account.provider_name)
    }
    return providers
  }, [])
})

const isCommentsContentType = computed(() => {
  return getActiveAccounts().some(account => account.content_type === 'comments')
})

const threadBtnInfo = computed(() => {
  const items = getAccountVersion(props.form.versions, activeVersion.value)?.content || []
  const isOriginalVersion = activeVersion.value === 0
  const selectedAccount = getAccount(selectedAccounts.value, activeVersion.value)
  const hasComments = selectedAccounts.value.some(account => account.content_type === 'comments')
  const hasThread = selectedAccounts.value.some(account => account.content_type === 'thread')
  const contentType = selectedAccount?.content_type

  if (isOriginalVersion && hasComments) {
    return { show: items.length <= 1, type: 'comments', name: $t('post.add_first_comment') }
  }

  if (isOriginalVersion && hasThread) {
    return { show: true, type: 'thread', name: $t('post.add_post') }
  }

  if (!isOriginalVersion) {
    if (contentType === 'comments') {
      return { show: items.length <= 1, type: 'comments', name: $t('post.add_first_comment') }
    }

    if (contentType === 'thread') {
      return { show: true, type: 'thread', name: $t('post.add_post') }
    }
  }

  return { show: false, type: '', name: '' }
})

const accountsWithoutVersion = computed(() =>
  getAccountsWithoutVersion(props.form.versions, selectedAccounts.value)
)

const providersPostConfigs = computed(() => {
  const versionObj = getAccountVersion(props.form.versions, activeVersion.value)
  const accounts =
    activeVersion.value === 0
      ? uniqBy(accountsWithoutVersion.value, 'provider')
      : selectedAccounts.value.filter(account => account.id === activeVersion.value)

  return accounts.map(account => {
    const type = versionObj?.options[account.provider]?.type || 'default'
    const enableThumb = account.post_configs.enable_video_thumb[type]

    return {
      provider: account.provider,
      provider_name: account.provider_name,
      enableThumb
    }
  })
})

const providersWithVideoThumbEnabled = computed(() => {
  return providersPostConfigs.value
    .filter(provider => provider.enableThumb)
    .map(provider => provider.provider_name)
})

const enableVideoThumb = computed(() => {
  return providersPostConfigs.value.some(provider => provider.enableThumb)
})

const addContentItem = afterIndex => {
  const items = getAccountVersion(props.form.versions, activeVersion.value)?.content || []
  const isOriginalVersion = activeVersion.value === 0

  // Don't allow adding more than 1 comment for an original version
  if (
    isOriginalVersion &&
    items.length > 1 &&
    selectedAccounts.value.some(account => account.content_type === 'comments')
  ) {
    return
  }

  // Don't allow adding more than 1 comment for a specific version
  if (
    !isOriginalVersion &&
    items.length > 1 &&
    getAccount(selectedAccounts.value, activeVersion.value)?.content_type === 'comments'
  ) {
    return
  }

  const newIndex = afterIndex + 1

  items.splice(newIndex, 0, versionContentObject())

  openContentItem(newIndex)
}

const openContentItem = index => {
  content.value.forEach((item, idx) => {
    item.opened = idx === index
  })

  nextTick(() => {
    focusEditor({ editorId: `postEditor-${index}` })
  })
}

const removeContentItem = index => {
  if (content.value.length === 1) {
    return
  }

  content.value.splice(index, 1)
  // Calculate the new index to open. If the removed item was the first, open the first item after removal.
  // Otherwise, open the item just before the removed one.
  const newIndex = index === 0 ? 0 : index - 1

  openContentItem(newIndex)

  nextTick(() => {
    emitter.emit('postVersionContentDeleted')
  })
}

const moveContentItemOneStepUp = index => {
  if (index > 0) {
    const item = content.value[index]

    // Remove the item from its current
    content.value.splice(index, 1)
    // Insert the item one position up
    content.value.splice(index - 1, 0, item)
  }
}

const moveContentItemOneStepBottom = index => {
  if (index < content.value.length - 1) {
    const item = content.value[index]

    // Remove the item from its current position
    content.value.splice(index, 1)
    // Insert the item one position down
    content.value.splice(index + 1, 0, item)
  }
}

watch(
  () => props.form.accounts,
  () => {
    setupVersions()
  }
)

const { insertEmoji, insertContent, replaceContent, focusEditor } = useEditor()

onMounted(() => {
  setupVersions()

  setupURLMetaForAllVersions(props.form.versions)
})
</script>
<template>
  <div class="flex flex-wrap items-center gap-sm mb-lg">
    <template v-for="account in $page.props.accounts" :key="account.id">
      <button :disabled="isAccountUnselectable(account)" @click="selectAccount(account.id)">
        <Account
          v-tooltip="account.name"
          :provider="account.provider"
          :name="account.name"
          :img-url="account.image"
          :warning-message="
            isAccountUnselectable(account)
              ? $t('post.no_simultaneous_post', { provider: account.provider_name })
              : ''
          "
          :active="isAccountSelected(account)"
        />
      </button>
    </template>
  </div>

  <Panel>
    <PostVersionsTab
      v-if="form.accounts.length > 1"
      :versions="form.versions"
      :active-version="activeVersion"
      :accounts="$page.props.accounts"
      :selected-accounts="form.accounts"
      class="mb-sm"
      @add="
        accountId => {
          addVersion(accountId)
        }
      "
      @remove="
        accountId => {
          removeVersion(accountId)
          resetActiveVersion()
        }
      "
      @select="
        accountId => {
          activeVersion = accountId
        }
      "
    />

    <PostVersionOptions
      :selected-accounts="selectedAccounts"
      :versions="form.versions"
      :active-version="activeVersion"
    />

    <Flex :col="true">
      <template v-for="(item, index) in content" :key="index">
        <div class="relative" :class="{ 'mt-sm first:mt-0': isCommentsContentType && index === 1 }">
          <template v-if="isCommentsContentType && index === 1">
            <Badge class="absolute left-0 -mt-sm ml-sm z-20">
              <ChatBubbleBottomCenterText class="mr-xs h-5! w-5!" />
              {{ $t('post.first_comment') }}
            </Badge>
          </template>

          <template v-if="item.opened">
            <template v-if="contentItemsCount > 1 && editAllowed">
              <div class="absolute top-0 right-0 -mr-sm mt-md">
                <Flex col gap="gap-0" class="border border-gray-200 rounded-md bg-white shadow-mix">
                  <PureButton
                    destructive
                    @click="
                      () => {
                        confirmation()
                          .title($t('general.delete'))
                          .description($t('page.are_you_sure'))
                          .destructive()
                          .btnConfirmName($t('general.delete'))
                          .onConfirm(dialog => {
                            removeContentItem(index)
                            dialog.close()
                          })
                          .show()
                      }
                    "
                  >
                    <X />
                  </PureButton>

                  <MenuDelimiter />

                  <template v-if="index !== 0">
                    <PureButton @click="moveContentItemOneStepUp(index)">
                      <ChevronUp />
                    </PureButton>
                  </template>

                  <template v-if="index < contentItemsCount - 1">
                    <PureButton @click="moveContentItemOneStepBottom(index)">
                      <ChevronDown />
                    </PureButton>
                  </template>
                </Flex>
              </div>
            </template>

            <Editor
              :id="`postEditor-${index}`"
              :value="item.body"
              :editable="editAllowed"
              :label="postCtx.editor.label"
              :placeholder="postCtx.editor.placeholder"
              @update="updateContent(index, 'body', $event)"
            >
              <template #default>
                <PostMedia
                  :media="item.media"
                  :video-thumbs="item.video_thumbs"
                  :enable-video-thumb="enableVideoThumb && !(isCommentsContentType && index === 1)"
                  :providers-with-video-thumb-enabled="providersWithVideoThumbEnabled"
                  @updated="updateContent(index, 'media', $event)"
                  @video-thumbs-updated="updateContent(index, 'video_thumbs', $event)"
                />

                <Flex
                  :responsive="false"
                  class="relative justify-between border-t border-gray-200 pt-md mt-md"
                >
                  <div v-if="!editAllowed" class="top-0 left-0 absolute w-full h-full z-10"></div>

                  <Flex :responsive="false">
                    <EmojiPicker
                      @selected="insertEmoji({ editorId: `postEditor-${index}`, emoji: $event })"
                      @close="focusEditor({ editorId: `postEditor-${index}` })"
                    />

                    <PostAddMedia
                      @insert="
                        $event => {
                          updateContent(index, 'media', [...item.media, ...$event.items])

                          if ($event.crediting) {
                            insertContent({
                              editorId: `postEditor-${index}`,
                              text: $event.crediting
                            })
                          }
                        }
                      "
                    />

                    <HashtagManager
                      :edit-allowed="editAllowed"
                      @insert="insertContent({ editorId: `postEditor-${index}`, text: $event })"
                    />

                    <VariableManager
                      :edit-allowed="editAllowed"
                      @insert="insertContent({ editorId: `postEditor-${index}`, text: $event })"
                    />

                    <TemplateManager
                      :post-content="content"
                      @insert="
                        $event => {
                          updateContent(index, 'media', $event.content[0].media)
                          insertContent({
                            editorId: `postEditor-${index}`,
                            text: $event.content[0].body
                          })
                        }
                      "
                    />

                    <template v-if="$page.props.ai_is_ready_to_use">
                      <AIAssist
                        :text="item.body"
                        :character-limit="currentMaxCharLimit"
                        @insert="insertContent({ editorId: `postEditor-${index}`, text: $event })"
                        @replace="replaceContent({ editorId: `postEditor-${index}`, text: $event })"
                      />
                    </template>
                    <div v-if="$page.props.general_configs.url_shortener_provider !== 'disabled'">
                      <EditorButton
                        v-tooltip="
                          urlShortenerActivated ? $t('url_shortener.on') : $t('url_shortener.off')
                        "
                        :active="urlShortenerActivated"
                        @click="
                          () => {
                            urlShortenerActivated = !urlShortenerActivated
                            shortenUrls()
                          }
                        "
                      >
                        <Link />
                      </EditorButton>
                    </div>
                  </Flex>

                  <Flex :responsive="false">
                    <PostCharacterCount
                      :selected-accounts="selectedAccounts"
                      :versions="form.versions"
                      :active-version="activeVersion"
                      :active-content="index"
                      @update-max-char-limit="currentMaxCharLimit = $event"
                    />

                    <template v-if="threadBtnInfo.show">
                      <EditorButton
                        v-tooltip="threadBtnInfo.name"
                        class="flex items-center text-primary-500!"
                        @click="addContentItem(index)"
                      >
                        <Plus />
                      </EditorButton>
                    </template>
                  </Flex>
                </Flex>
              </template>
            </Editor>

            <template v-if="selectedAccounts.length">
              <PostContentValidator
                :selected-accounts="selectedAccounts"
                :active-version="activeVersion"
                :active-content="index"
                :versions="form.versions"
              />
            </template>
          </template>

          <template v-if="!item.opened">
            <div
              role="button"
              class="relative border border-gray-200 rounded-md p-md"
              @click="openContentItem(index)"
            >
              <div
                class="top-0 left-0 absolute w-full h-full z-10 bg-white/50 hover:bg-white/30 transition ease-in-out duration-200"
              ></div>
              <EditorReadOnly :value="item.body" />
              <PostMedia
                :media="item.media"
                :video-thumbs="item.video_thumbs"
                :show-item-dropdown-menu="false"
                :enable-video-thumb="enableVideoThumb"
                @updated="updateContent(index, 'media', $event)"
                @video-thumbs-updated="updateContent(index, 'video_thumbs', $event)"
              />
            </div>
          </template>
        </div>
      </template>

      <template
        v-if="
          content.length > 1 && threadBtnInfo.type === 'comments' && singleContentProviders.length
        "
      >
        <AlertText variant="warning" class="mt-xs">
          {{ $t('post.no_first_comment', { providers: singleContentProviders.join(', ') }) }}
        </AlertText>
      </template>

      <template v-if="isCommentsContentType && content.length > 2">
        <AlertText variant="warning" class="mt-xs">
          {{ $t('post.only_one_first_comment') }}
        </AlertText>
      </template>
    </Flex>
  </Panel>
</template>
