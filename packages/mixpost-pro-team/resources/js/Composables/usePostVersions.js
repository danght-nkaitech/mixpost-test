const usePostVersions = () => {
  const versionContentObject = (
    body = '',
    media = [],
    video_thumbs = [],
    url = '',
    opened = true
  ) => {
    return {
      body,
      media,
      video_thumbs,
      url,
      opened
    }
  }

  const versionOptions = (title, link) => {
    return {
      mastodon: {
        sensitive: false
      },
      facebook_page: {
        type: 'post' // post, reel...etc.
      },
      instagram: {
        type: 'post' // post, reel...etc.
      },
      youtube: {
        title,
        status: 'public',
        made_for_kids: false
      },
      gbp: {
        type: 'post',
        button: 'NONE',
        button_link: '',
        offer_has_details: false,
        coupon_code: '',
        offer_link: '',
        terms: '',
        event_title: '',
        start_date: null,
        end_date: null,
        event_has_time: false,
        start_time: '09:00',
        end_time: '17:00'
      },
      pinterest: {
        title,
        link,
        boards: {
          'account-0': null
        }
      },
      linkedin: {
        visibility: 'PUBLIC'
      },
      tiktok: {
        privacy_level: {
          'account-0': ''
        },
        allow_comments: {
          'account-0': false
        },
        allow_duet: {
          'account-0': false
        },
        allow_stitch: {
          'account-0': false
        },
        is_aigc: {
          'account-0': false
        },
        content_disclosure: {
          'account-0': false
        },
        brand_organic_toggle: {
          'account-0': false
        },
        brand_content_toggle: {
          'account-0': false
        }
      }
    }
  }

  const versionObject = (
    accountId = 0,
    isOriginal = false,
    contentBody = '',
    media = [],
    video_thumbs = [],
    title = '',
    link = ''
  ) => {
    return {
      account_id: accountId,
      is_original: isOriginal,
      content: [versionContentObject(contentBody, media, video_thumbs, link)],
      options: versionOptions(title, link)
    }
  }

  const getOriginalVersion = versions => {
    const find = versions.find(version => {
      return version.is_original && version.account_id === 0
    })

    return find ? find : null
  }

  const getAccountVersion = (versions, accountId) => {
    const find = versions.find(version => {
      return version.account_id === accountId
    })

    return find ? find : null
  }

  const getIndexAccountVersion = (versions, accountId) => {
    return versions.findIndex(version => version.account_id === accountId)
  }

  const getAccountsWithoutVersion = (versions, selectedAccounts) => {
    return selectedAccounts.filter(account => !accountHasVersion(versions, account.id))
  }

  const accountHasVersion = (versions, accountId) => {
    return versions.some(version => version.account_id === accountId)
  }

  return {
    versionContentObject,
    versionObject,
    getOriginalVersion,
    getAccountVersion,
    getIndexAccountVersion,
    getAccountsWithoutVersion,
    accountHasVersion
  }
}

export default usePostVersions
