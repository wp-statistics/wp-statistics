import { describe, expect, it } from 'vitest'

import {
  parseUrl,
  extractUtmCampaign,
  extractUtmParam,
  getUrlPath,
  getUrlQueryString,
  formatQueryStringForDisplay,
  truncateUrl,
  isExternalUrl,
  parseEntryPage,
} from '@lib/url-utils'

describe('parseUrl', () => {
  describe('Basic Parsing', () => {
    it('should parse URL without query string', () => {
      const result = parseUrl('/page/about')

      expect(result.path).toBe('/page/about')
      expect(result.hasQueryString).toBe(false)
      expect(result.queryString).toBeUndefined()
    })

    it('should parse URL with query string', () => {
      const result = parseUrl('/search?q=test&page=1')

      expect(result.path).toBe('/search')
      expect(result.hasQueryString).toBe(true)
      expect(result.queryString).toBe('q=test&page=1')
    })

    it('should handle root path', () => {
      const result = parseUrl('/')

      expect(result.path).toBe('/')
      expect(result.hasQueryString).toBe(false)
    })

    it('should handle empty string as root', () => {
      const result = parseUrl('')

      expect(result.path).toBe('/')
      expect(result.hasQueryString).toBe(false)
    })

    it('should handle URL with only query string', () => {
      const result = parseUrl('?foo=bar')

      expect(result.path).toBe('/')
      expect(result.hasQueryString).toBe(true)
      expect(result.queryString).toBe('foo=bar')
    })
  })

  describe('Complex URLs', () => {
    it('should handle multiple query parameters', () => {
      const result = parseUrl('/products?category=electronics&brand=apple&sort=price')

      expect(result.path).toBe('/products')
      expect(result.queryString).toBe('category=electronics&brand=apple&sort=price')
    })

    it('should handle URL with special characters in path', () => {
      const result = parseUrl('/products/item%20name')

      expect(result.path).toBe('/products/item%20name')
    })

    it('should handle empty query string marker', () => {
      const result = parseUrl('/page?')

      expect(result.path).toBe('/page')
      expect(result.hasQueryString).toBe(true)
      expect(result.queryString).toBe('')
    })
  })
})

describe('extractUtmCampaign', () => {
  it('should extract utm_campaign from query string', () => {
    const result = extractUtmCampaign('utm_campaign=summer_sale&source=email')

    expect(result).toBe('summer_sale')
  })

  it('should return undefined when utm_campaign is not present', () => {
    const result = extractUtmCampaign('source=google&medium=cpc')

    expect(result).toBeUndefined()
  })

  it('should return undefined for undefined input', () => {
    const result = extractUtmCampaign(undefined)

    expect(result).toBeUndefined()
  })

  it('should return undefined for empty string', () => {
    const result = extractUtmCampaign('')

    expect(result).toBeUndefined()
  })

  it('should handle utm_campaign with special characters', () => {
    const result = extractUtmCampaign('utm_campaign=summer%20sale%202024')

    expect(result).toBe('summer sale 2024')
  })
})

describe('extractUtmParam', () => {
  const queryString = 'utm_campaign=launch&utm_source=google&utm_medium=cpc&utm_term=keyword&utm_content=ad1'

  it('should extract utm_campaign', () => {
    expect(extractUtmParam(queryString, 'campaign')).toBe('launch')
  })

  it('should extract utm_source', () => {
    expect(extractUtmParam(queryString, 'source')).toBe('google')
  })

  it('should extract utm_medium', () => {
    expect(extractUtmParam(queryString, 'medium')).toBe('cpc')
  })

  it('should extract utm_term', () => {
    expect(extractUtmParam(queryString, 'term')).toBe('keyword')
  })

  it('should extract utm_content', () => {
    expect(extractUtmParam(queryString, 'content')).toBe('ad1')
  })

  it('should return undefined for missing param', () => {
    expect(extractUtmParam('utm_source=google', 'campaign')).toBeUndefined()
  })

  it('should return undefined for undefined query string', () => {
    expect(extractUtmParam(undefined, 'campaign')).toBeUndefined()
  })
})

describe('getUrlPath', () => {
  it('should return path without query string', () => {
    expect(getUrlPath('/page/about?foo=bar')).toBe('/page/about')
  })

  it('should return path when no query string', () => {
    expect(getUrlPath('/page/about')).toBe('/page/about')
  })

  it('should return root for empty string', () => {
    expect(getUrlPath('')).toBe('/')
  })

  it('should handle root with query string', () => {
    expect(getUrlPath('/?foo=bar')).toBe('/')
  })
})

describe('getUrlQueryString', () => {
  it('should return query string without leading ?', () => {
    expect(getUrlQueryString('/page?foo=bar&baz=qux')).toBe('foo=bar&baz=qux')
  })

  it('should return undefined when no query string', () => {
    expect(getUrlQueryString('/page')).toBeUndefined()
  })

  it('should return empty string for URL with just ?', () => {
    expect(getUrlQueryString('/page?')).toBe('')
  })
})

describe('formatQueryStringForDisplay', () => {
  it('should add leading ? to query string', () => {
    expect(formatQueryStringForDisplay('foo=bar')).toBe('?foo=bar')
  })

  it('should return undefined for undefined input', () => {
    expect(formatQueryStringForDisplay(undefined)).toBeUndefined()
  })

  it('should return undefined for empty string', () => {
    expect(formatQueryStringForDisplay('')).toBeUndefined()
  })

  it('should handle query string with special characters', () => {
    expect(formatQueryStringForDisplay('search=hello%20world')).toBe('?search=hello%20world')
  })
})

describe('truncateUrl', () => {
  it('should not truncate URL shorter than maxLength', () => {
    expect(truncateUrl('/short', 20)).toBe('/short')
  })

  it('should truncate URL longer than maxLength', () => {
    const longUrl = '/this/is/a/very/long/url/path'
    const result = truncateUrl(longUrl, 15)

    expect(result).toBe('/this/is/a/v...')
    expect(result.length).toBe(15)
  })

  it('should handle URL exactly at maxLength', () => {
    const url = '/exactly'
    expect(truncateUrl(url, 8)).toBe('/exactly')
  })

  it('should not truncate URL at exact maxLength', () => {
    // URL length equals maxLength, should not truncate
    const result = truncateUrl('/page', 5)

    expect(result).toBe('/page')
    expect(result.length).toBe(5)
  })

  it('should truncate URL longer than maxLength by 1', () => {
    // URL is 6 chars, maxLength is 5 - should truncate
    const result = truncateUrl('/pages', 5)

    expect(result).toBe('/p...')
    expect(result.length).toBe(5)
  })

  it('should handle empty URL', () => {
    expect(truncateUrl('', 10)).toBe('')
  })
})

describe('isExternalUrl', () => {
  describe('Without Current Domain', () => {
    it('should return true for external URLs', () => {
      expect(isExternalUrl('https://example.com/page')).toBe(true)
    })

    it('should return false for localhost URLs', () => {
      expect(isExternalUrl('/page')).toBe(false)
    })

    it('should return false for relative URLs', () => {
      expect(isExternalUrl('/about')).toBe(false)
    })
  })

  describe('With Current Domain', () => {
    it('should return false when domain matches', () => {
      expect(isExternalUrl('https://mysite.com/page', 'mysite.com')).toBe(false)
    })

    it('should return true when domain differs', () => {
      expect(isExternalUrl('https://external.com/page', 'mysite.com')).toBe(true)
    })

    it('should handle subdomain differences', () => {
      expect(isExternalUrl('https://blog.mysite.com/page', 'mysite.com')).toBe(true)
    })
  })

  describe('Relative URLs treated as localhost paths', () => {
    // The implementation uses new URL(url, 'http://localhost') as base
    // So relative URLs become paths on localhost
    it('should treat path-like strings as relative to localhost', () => {
      // 'not-a-url' becomes http://localhost/not-a-url
      // hostname is 'localhost' !== 'mysite.com', so returns true (external)
      expect(isExternalUrl('not-a-url', 'mysite.com')).toBe(true)
    })

    it('should treat path-like strings as local when no domain provided', () => {
      // 'not-a-url' becomes http://localhost/not-a-url
      // hostname is 'localhost', returns false (not external)
      expect(isExternalUrl('not-a-url')).toBe(false)
    })
  })
})

describe('parseEntryPage', () => {
  describe('Basic Parsing', () => {
    it('should parse URL with title', () => {
      const result = parseEntryPage('/page/about', 'About Us')

      expect(result.path).toBe('/page/about')
      expect(result.title).toBe('About Us')
      expect(result.hasQueryString).toBe(false)
      expect(result.queryString).toBeUndefined()
      expect(result.utmCampaign).toBeUndefined()
    })

    it('should parse URL with query string and UTM', () => {
      const result = parseEntryPage('/landing?utm_campaign=summer_sale', 'Landing Page')

      expect(result.path).toBe('/landing')
      expect(result.hasQueryString).toBe(true)
      expect(result.queryString).toBe('?utm_campaign=summer_sale')
      expect(result.utmCampaign).toBe('summer_sale')
    })

    it('should use URL as title when title is not provided', () => {
      const result = parseEntryPage('/page/about')

      expect(result.title).toBe('/page/about')
    })

    it('should use URL as title when title is null', () => {
      const result = parseEntryPage('/page/about', null)

      expect(result.title).toBe('/page/about')
    })
  })

  describe('Edge Cases', () => {
    it('should handle undefined URL', () => {
      const result = parseEntryPage(undefined)

      expect(result.path).toBe('/')
      expect(result.title).toBe('Unknown')
    })

    it('should handle null URL', () => {
      const result = parseEntryPage(null)

      expect(result.path).toBe('/')
      expect(result.title).toBe('Unknown')
    })

    it('should handle empty URL', () => {
      const result = parseEntryPage('', 'Empty Page')

      expect(result.path).toBe('/')
      expect(result.title).toBe('Empty Page')
    })
  })

  describe('Query String Formatting', () => {
    it('should format query string with leading ?', () => {
      const result = parseEntryPage('/page?foo=bar')

      expect(result.queryString).toBe('?foo=bar')
    })

    it('should not include query string when not present', () => {
      const result = parseEntryPage('/page')

      expect(result.queryString).toBeUndefined()
    })
  })
})
