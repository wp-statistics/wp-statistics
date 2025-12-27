/**
 * URL parsing and manipulation utilities
 * Extracted from route files to reduce code duplication
 */

export interface ParsedUrl {
  path: string
  queryString?: string
  hasQueryString: boolean
}

/**
 * Parse a URL into its path and query string components
 */
export const parseUrl = (url: string): ParsedUrl => {
  const hasQueryString = url.includes('?')
  const [path, queryString] = url.split('?')
  return {
    path: path || '/',
    queryString: hasQueryString ? queryString : undefined,
    hasQueryString,
  }
}

/**
 * Extract UTM campaign from a query string
 */
export const extractUtmCampaign = (queryString: string | undefined): string | undefined => {
  if (!queryString) return undefined
  try {
    const params = new URLSearchParams(queryString)
    return params.get('utm_campaign') || undefined
  } catch {
    return undefined
  }
}

/**
 * Extract a specific UTM parameter from a query string
 */
export const extractUtmParam = (
  queryString: string | undefined,
  param: 'campaign' | 'source' | 'medium' | 'term' | 'content'
): string | undefined => {
  if (!queryString) return undefined
  try {
    const params = new URLSearchParams(queryString)
    return params.get(`utm_${param}`) || undefined
  } catch {
    return undefined
  }
}

/**
 * Get the path portion of a URL (without query string)
 */
export const getUrlPath = (url: string): string => {
  return url.split('?')[0] || '/'
}

/**
 * Get the query string portion of a URL (without the leading ?)
 */
export const getUrlQueryString = (url: string): string | undefined => {
  const parts = url.split('?')
  return parts.length > 1 ? parts[1] : undefined
}

/**
 * Format query string for display (adds leading ?)
 */
export const formatQueryStringForDisplay = (queryString: string | undefined): string | undefined => {
  if (!queryString) return undefined
  return `?${queryString}`
}

/**
 * Truncate a URL to a maximum length
 */
export const truncateUrl = (url: string, maxLength: number): string => {
  if (url.length <= maxLength) return url
  return `${url.substring(0, maxLength - 3)}...`
}

/**
 * Check if a URL is external (different domain)
 */
export const isExternalUrl = (url: string, currentDomain?: string): boolean => {
  try {
    const urlObj = new URL(url, 'http://localhost')
    if (!currentDomain) return urlObj.hostname !== 'localhost'
    return urlObj.hostname !== currentDomain
  } catch {
    return false
  }
}

/**
 * Parse entry page data from a raw URL
 * Returns all the commonly needed fields for entry page display
 */
export const parseEntryPage = (
  url: string | undefined | null,
  title?: string | undefined | null
): {
  path: string
  title: string
  hasQueryString: boolean
  queryString?: string
  utmCampaign?: string
} => {
  const rawUrl = url || '/'
  const parsed = parseUrl(rawUrl)

  return {
    path: parsed.path,
    title: title || url || 'Unknown',
    hasQueryString: parsed.hasQueryString,
    queryString: parsed.hasQueryString ? `?${parsed.queryString}` : undefined,
    utmCampaign: extractUtmCampaign(parsed.queryString),
  }
}
