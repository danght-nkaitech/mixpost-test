import CountTextCharacters from '../Util/CountTextCharacters'

const shortenUrl = url => {
  try {
    const parsedUrl = new URL(url)
    if (parsedUrl.protocol !== 'http:' && parsedUrl.protocol !== 'https:') {
      return url
    }

    const path =
      (parsedUrl.pathname === '/' ? '' : parsedUrl.pathname) + parsedUrl.search + parsedUrl.hash
    if (path.length > 15) {
      return `${parsedUrl.host + path.slice(0, 13)}...`
    }

    return parsedUrl.host + path
  } catch {
    return url
  }
}

const getPostLength = content => {
  // Replace full URLs with shortened versions
  const sanitizedContent = content.replace(/https?:\/\/[^\s]+/g, match => shortenUrl(match))

  return CountTextCharacters.getLength(sanitizedContent, {
    urlWeight: null,
    emojiWeight: 1
  })
}

export default {
  getPostLength,
  shortenUrl
}
