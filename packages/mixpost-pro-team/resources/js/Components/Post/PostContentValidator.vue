<script setup>
import { onBeforeUnmount, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import emitter from '@/Services/emitter'
import { debounce, filter } from 'lodash'
import usePostValidator from '../../Composables/usePostValidator'
import useEditor from '@/Composables/useEditor'
import usePostCharacterLimit from '../../Composables/usePostCharacterLimit'
import usePostMediaLimit from '../../Composables/usePostMediaLimit'

const { t: $t } = useI18n()

const props = defineProps({
  selectedAccounts: {
    type: Array,
    required: true
  },
  versions: {
    type: Array,
    required: true
  },
  activeVersion: {
    type: Number,
    required: true
  },
  activeContent: {
    type: Number,
    required: true
  }
})

const mediaErrorGroup = 'media'
const charErrorGroup = 'char_limit'

const { addAccountError, removeAccountError, removeError } = usePostValidator()
const { getTextFromHtmlString } = useEditor()

const getAccount = accountId => {
  return props.selectedAccounts.find(account => account.id === accountId) || null
}

const isAccountSelected = accountId =>
  props.selectedAccounts.some(account => account.id === accountId)

const getEnabledVersions = () =>
  filter(
    props.versions,
    version => version.account_id === 0 || isAccountSelected(version.account_id)
  )

const clearErrors = () => {
  removeError({ group: mediaErrorGroup })
  removeError({ group: charErrorGroup })
}

// const { getRequirementOperator } = usePostRequirementOperator(props)

const {
  currentCharMaxLimit,
  // currentCharMinLimit,
  // currentCharUsed,
  currentCharLeft,
  getCharMaxLimit,
  // getCharMinLimit,
  getTextLength,
  calculateCharLeft
} = usePostCharacterLimit(props)

const handleCharMaxLimitError = ({
  charLimit,
  charLeft,
  contentIndex,
  isThread,
  accountId,
  accountName,
  providerName
}) => {
  if (charLeft < 0) {
    const maxCharMessage = $t('post.rules.max_char', { count: charLimit })

    addAccountError({
      group: charErrorGroup,
      key: `c_${contentIndex}`,
      message: isThread
        ? `${$t('post.post')} #${contentIndex + 1} - ${maxCharMessage}`
        : maxCharMessage,
      accountId,
      accountName,
      providerName
    })
  } else {
    removeAccountError({
      group: charErrorGroup,
      key: `c_${contentIndex}`,
      accountId
    })
  }
}

// TODO: Refactor this function
// const handleCharMinLimitError = ({
//   charLimit,
//   charUsed,
//   contentIndex,
//   isThread,
//   accountId,
//   accountName,
//   providerName
// }) => {
//   if (charLimit > charUsed) {
//     const minCharMessage = $t('post.rules.min_char', { count: charLimit })
//
//     addAccountError({
//       group: charErrorGroup,
//       key: `c_min_${contentIndex}`,
//       message: isThread
//         ? `${$t('post.post')} #${contentIndex + 1} - ${minCharMessage}`
//         : minCharMessage,
//       accountId,
//       accountName,
//       providerName
//     })
//   } else {
//     removeAccountError({
//       group: charErrorGroup,
//       key: `c_min_${contentIndex}`,
//       accountId
//     })
//   }
// }

// TODO: Refactor this function
// const handleRequirementOperationOr = ({
//   usedChar,
//   usedMedia,
//   charMinLimit,
//   mediaMinLimit,
//   contentIndex,
//   isThread,
//   accountId,
//   accountName,
//   providerName
// }) => {
//   const charMinCondition = {
//     passes: !(usedChar < charMinLimit),
//     message: $t('post.rules.min_char', { count: charMinLimit }),
//     providerName
//   }
//
//   const mediaMinCondition = getMediaMinCondition({
//     used: usedMedia,
//     limits: mediaMinLimit,
//     contentIndex,
//     accountId
//   })
//
//   const messageArray = []
//
//   if (!charMinCondition.passes) {
//     messageArray.push(charMinCondition.message)
//   }
//
//   if (!mediaMinCondition.passes) {
//     messageArray.push(mediaMinCondition.message)
//   }
//
//   const message = messageArray.join(' or ')
//
//   if (
//     charMinCondition.providerName === mediaMinCondition.provider &&
//     messageArray.length === (charMinLimit === 0 ? 1 : 2)
//   ) {
//     addAccountError({
//       group: charErrorGroup,
//       key: `c_min_o_${contentIndex}`,
//       message: isThread ? `${$t('post.post')} #${contentIndex + 1} - ${message}` : `${message}`,
//       accountId,
//       accountName,
//       providerName
//     })
//
//     return
//   }
//
//   if (charMinCondition.providerName !== mediaMinCondition.provider) {
//     removeAccountError({
//       group: charErrorGroup,
//       key: `c_min_o_char_${contentIndex}`,
//       accountId
//     })
//
//     removeAccountError({
//       group: charErrorGroup,
//       key: `c_min_o_media_${contentIndex}`,
//       accountId
//     })
//
//     if (!charMinCondition.passes) {
//       addAccountError({
//         group: charErrorGroup,
//         key: `c_min_o_char_${contentIndex}`,
//         message: isThread
//           ? `${$t('post.post')} #${contentIndex + 1} - ${charMinCondition.message}`
//           : `${charMinCondition.message}`,
//         accountId,
//         accountName,
//         providerName: charMinCondition.providerName
//       })
//     }
//
//     if (!mediaMinCondition.passes) {
//       addAccountError({
//         group: charErrorGroup,
//         key: `c_min_o_media_${contentIndex}`,
//         message: isThread
//           ? `${$t('post.post')} #${contentIndex + 1} - ${mediaMinCondition.message}`
//           : `${mediaMinCondition.message}`,
//         accountId,
//         accountName,
//         providerName: mediaMinCondition.provider
//       })
//     }
//
//     return
//   }
//
//   removeAccountError({
//     group: charErrorGroup,
//     key: `c_min_o_${contentIndex}`,
//     accountId
//   })
// }

const {
  mediaTypesBasic,
  currentMediaMaxLimits,
  // currentMediaMinLimits,
  currentMediaUsed,
  // getMediaMinLimits,
  getMediaMaxLimits,
  getMediaLength
} = usePostMediaLimit(props)

const handleMediaMaxLimitError = ({
  used,
  limits,
  contentIndex,
  isThread,
  accountId,
  accountName
}) => {
  mediaTypesBasic.forEach(type => {
    if (used[type] > limits[type].limit) {
      addAccountError({
        group: mediaErrorGroup,
        key: `c_${contentIndex}_${type}`,
        message: isThread
          ? `${$t('post.post')} #${contentIndex + 1} - ${$t(`post.rules.max_${type}`, limits[type].limit)}`
          : $t(`post.rules.max_${type}`, limits[type].limit),
        accountId,
        accountName,
        providerName: limits[type].provider
      })
    } else {
      removeAccountError({ group: mediaErrorGroup, key: `c_${contentIndex}_${type}`, accountId })
    }
  })

  if (used.mixing && !limits.allow_mixing.limit) {
    addAccountError({
      group: mediaErrorGroup,
      key: `c_${contentIndex}_mixing`,
      message: isThread
        ? `${$t('post.post')} #${contentIndex + 1} - ${$t('post.rules.no_mixed_media')}`
        : $t('post.rules.no_mixed_media'),
      accountId,
      accountName,
      providerName: limits.allow_mixing.provider
    })
  } else {
    removeAccountError({ group: mediaErrorGroup, key: `c_${contentIndex}_mixing`, accountId })
  }
}

// const getMediaMinCondition = ({ used, limits, contentIndex, accountId }) => {
//   const requiredMedia = Object.keys(limits).filter(format => limits[format] !== null)
//
//   if (!requiredMedia.length)
//     return {
//       passes: true,
//       message: '',
//       provider: ''
//     }
//
//   // TODO: rename to providerName, because this is not an object.
//   const provider = limits[requiredMedia[0]].provider // All media types have the same provider
//   let message = ''
//
//   // Check if any media type with a limit is already used
//   const unmetLimits = requiredMedia.filter(
//     mediaType => limits[mediaType].limit > 0 && used[mediaType] < limits[mediaType].limit
//   )
//
//   if (!unmetLimits.length) {
//     removeAccountError({ group: mediaErrorGroup, key: `c_m_min_${contentIndex}`, accountId })
//     return {
//       passes: true,
//       message,
//       provider
//     }
//   }
//
//   // If any type of media has met its limit, return null
//   for (const mediaType of Object.keys(used)) {
//     if (!Object.prototype.hasOwnProperty.call(limits, mediaType)) {
//       continue
//     }
//
//     if (limits[mediaType].limit !== 0 && used[mediaType] >= limits[mediaType].limit) {
//       removeAccountError({ group: mediaErrorGroup, key: `c_m_min_${contentIndex}`, accountId })
//       return {
//         passes: true,
//         message,
//         provider
//       }
//     }
//   }
//   // TODO: Translate message
//   // Construct message based on unmet limits
//   if (unmetLimits.length === 1) {
//     message = `A minimum of 1 ${unmetLimits[0].slice(0, -1)} is required.`
//   }
//
//   if (unmetLimits.length > 1) {
//     const mediaTypes = unmetLimits.map(mediaType => mediaType.slice(0, -1))
//     message = `A minimum of 1 ${mediaTypes.join(' or ')} is required.`
//   }
//
//   return {
//     passes: message === '',
//     message,
//     provider
//   }
// }

// TODO: Refactor this function
// const handleMediaMinLimitError = ({
//   used,
//   limits,
//   contentIndex,
//   isThread,
//   accountId,
//   accountName
// }) => {
//   const { passes, message, provider } = getMediaMinCondition({
//     used,
//     limits,
//     contentIndex,
//     accountId
//   })
//
//   if (!passes) {
//     addAccountError({
//       group: mediaErrorGroup,
//       key: `c_m_min_${contentIndex}`,
//       message: isThread ? `${$t('post.post')} #${contentIndex + 1} - ${message}` : message,
//       accountId,
//       accountName,
//       providerName: provider
//     })
//   }
// }

watch(
  [
    currentCharLeft,
    // currentCharMinLimit,
    currentCharMaxLimit,
    currentMediaMaxLimits,
    // currentMediaMinLimits,
    currentMediaUsed
  ],
  debounce(() => {
    const account = getAccount(props.activeVersion)
    const isThread = getEnabledVersions().some(
      version => version.account_id === props.activeVersion && version.content.length > 1
    )

    // const requirementOperator = getRequirementOperator(props.activeVersion);

    handleCharMaxLimitError({
      charLimit: currentCharMaxLimit.value.limit,
      charLeft: currentCharLeft.value,
      contentIndex: props.activeContent,
      isThread,
      accountId: props.activeVersion,
      accountName: account?.name || '',
      providerName: currentCharMaxLimit.value?.provider.name
    })

    if (currentMediaMaxLimits.value) {
      handleMediaMaxLimitError({
        used: currentMediaUsed.value,
        limits: currentMediaMaxLimits.value,
        contentIndex: props.activeContent,
        isThread,
        accountId: props.activeVersion,
        accountName: account?.name || ''
      })
    }

    // TODO: Refactor this function
    // if (requirementOperator && requirementOperator.operator === "or") {
    //     handleRequirementOperationOr({
    //         usedChar: currentCharUsed.value,
    //         usedMedia: currentMediaUsed.value,
    //         charMinLimit: currentCharMinLimit.value?.limit || 0,
    //         mediaMinLimit: currentMediaMinLimits.value,
    //         contentIndex: props.activeContent,
    //         isThread,
    //         accountId: props.activeVersion,
    //         accountName: account?.name || "",
    //         providerName: currentCharMinLimit.value?.provider.name,
    //     });
    // } else {
    //     handleCharMinLimitError({
    //         charLimit: currentCharMinLimit.value?.limit || 0,
    //         charUsed: currentCharUsed.value,
    //         contentIndex: props.activeContent,
    //         isThread,
    //         accountId: props.activeVersion,
    //         accountName: account?.name || "",
    //         providerName: currentCharMinLimit.value?.provider.name,
    //     });
    //
    //     if (currentMediaMinLimits.value) {
    //         handleMediaMinLimitError({
    //             used: currentMediaUsed.value,
    //             limits: currentMediaMinLimits.value,
    //             contentIndex: props.activeContent,
    //             isThread,
    //             accountId: props.activeVersion,
    //             accountName: account?.name || "",
    //         });
    //     }
    //
    // }
  }, 100)
)

const init = () => {
  getEnabledVersions().forEach(version => {
    const accountName = getAccount(version.account_id)?.name
    const isThread = version.content.length > 1

    const charMaxLimit = getCharMaxLimit(version.account_id)
    // const charMinLimit = getCharMinLimit(version.account_id);

    const mediaMaxLimits = getMediaMaxLimits(version.account_id)
    // const mediaMinLimits = getMediaMinLimits(version.account_id);

    // const requirementOperator = getRequirementOperator(version.account_id);

    version.content.forEach((item, index) => {
      const text = getTextFromHtmlString(item.body)
      const usedMedia = getMediaLength(item.media)

      if (charMaxLimit?.limit) {
        handleCharMaxLimitError({
          charLimit: charMaxLimit.limit,
          charLeft: calculateCharLeft(
            charMaxLimit.limit,
            getTextLength(charMaxLimit?.provider.id, text)
          ),
          contentIndex: index,
          isThread,
          accountId: version.account_id,
          accountName: accountName || '',
          providerName: charMaxLimit?.provider.name
        })
      }

      if (mediaMaxLimits) {
        handleMediaMaxLimitError({
          used: usedMedia,
          limits: mediaMaxLimits,
          contentIndex: index,
          isThread,
          accountId: version.account_id,
          accountName: accountName || ''
        })
      }

      // TODO: Refactor this function
      // if (requirementOperator && requirementOperator.operator === 'or') {
      //     const textLength = getTextLength(charMinLimit?.provider.id, text);
      //
      //     handleRequirementOperationOr({
      //         usedChar: textLength,
      //         usedMedia: usedMedia,
      //         charMinLimit: charMinLimit?.limit || 0,
      //         mediaMinLimit: mediaMinLimits,
      //         contentIndex: index,
      //         isThread,
      //         accountId: version.account_id,
      //         accountName: accountName || '',
      //         providerName: charMinLimit?.provider.name,
      //     })
      //
      //     return;
      // }
      //
      // if (requirementOperator && requirementOperator.operator === 'and') {
      //     handleCharMinLimitError({
      //         charLimit: charMinLimit?.limit || 0,
      //         charUsed: getTextLength(charMinLimit?.provider.id, text),
      //         contentIndex: index,
      //         isThread,
      //         accountId: version.account_id,
      //         accountName: accountName || '',
      //         providerName: charMinLimit?.provider.name,
      //     });
      //
      //     if (mediaMinLimits) {
      //         handleMediaMinLimitError({
      //             used: usedMedia,
      //             limits: mediaMinLimits,
      //             contentIndex: index,
      //             isThread,
      //             accountId: version.account_id,
      //             accountName: accountName || '',
      //         });
      //     }
      // }
    })
  })
}

const handleUpdate = () => {
  clearErrors()
  init()
}

watch(() => props.activeVersion, handleUpdate)
watch(() => props.versions.length, handleUpdate)
watch(() => props.selectedAccounts, handleUpdate)

onMounted(() => {
  emitter.on('postVersionContentDeleted', () => {
    handleUpdate()
  })

  handleUpdate()
})

onBeforeUnmount(() => {
  emitter.off('postVersionContentDeleted')
})
</script>
<template><div></div></template>
