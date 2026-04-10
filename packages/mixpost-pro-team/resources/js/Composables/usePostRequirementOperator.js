import { computed } from 'vue'
import { minBy } from 'lodash'
import usePostVersions from './usePostVersions'

const usePostRequirementOperator = props => {
  const { accountHasVersion } = usePostVersions()

  const accountsWithoutVersion = computed(() => {
    return props.selectedAccounts.filter(account => !accountHasVersion(props.versions, account.id))
  })

  const getRequirementOperatorForType = (postType, account) => {
    const rules = account.post_configs.media_text_requirement_operator

    if (Object.prototype.hasOwnProperty.call(rules, postType)) {
      return rules[postType]
    }

    return rules.default
  }

  const getHighestMediaTextRequirementOperator = version => {
    if (!props.selectedAccounts.length) return null

    const versionObj = props.versions.find(versionItem => versionItem.account_id === version)
    const accounts =
      version === 0
        ? accountsWithoutVersion.value
        : props.selectedAccounts.filter(account => account.id === version)

    const accountsLimit = accounts.map(account => {
      const postType = versionObj?.options[account.provider]?.type || 'default'
      const operator = getRequirementOperatorForType(postType, account)

      return {
        account_id: account.id,
        provider: {
          id: account.provider,
          name: account.provider_name
        },
        operator
      }
    })

    return accountsLimit.length ? minBy(accountsLimit, 'operator') : null
  }

  const getRequirementOperator = version => {
    return getHighestMediaTextRequirementOperator(version)
  }

  return {
    getRequirementOperator
  }
}

export default usePostRequirementOperator
