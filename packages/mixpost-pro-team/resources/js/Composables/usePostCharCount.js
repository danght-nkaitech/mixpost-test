import Mastodon from '../SocialProviders/Mastodon'
import Twitter from 'twitter-text'
import CountTextCharacters from '../Util/CountTextCharacters'

const usePostCharCount = () => {
  const getTextLength = (providerId, text) => {
    switch (providerId) {
      case 'mastodon':
        return Mastodon.getPostLength(text)
      case 'twitter':
        return Twitter.getTweetLength(text)
      default:
        return CountTextCharacters.getLength(text)
    }
  }

  const calculateCharLeft = (limit, used) => {
    return limit - used
  }

  return {
    getTextLength,
    calculateCharLeft
  }
}

export default usePostCharCount
