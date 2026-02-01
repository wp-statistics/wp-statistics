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

/**
 * Resource types that don't have individual analytics pages
 */
export const NON_LINKABLE_TYPES = new Set([
  'home',
  'search',
  '404',
  'archive',
  'date_archive',
  'post_type_archive',
  'feed',
  'loginpage',
  'unknown',
])

/**
 * Taxonomy types that link to /category/{termId}
 * These are stored as the taxonomy name (e.g., 'category', 'post_tag', 'product_cat')
 */
const TAXONOMY_TYPES = new Set([
  'category',
  'post_tag',
])

/**
 * Get the internal analytics route for a resource based on its type.
 *
 * @param pageType - The resource type (e.g., 'post', 'page', 'author_archive', 'category')
 * @param pageWpId - The WordPress ID (post ID, author ID, or term ID)
 * @param taxonomies - Optional list of registered taxonomy names for custom taxonomy detection
 * @param resourceId - Optional resources table PK for non-content pages (home, search, 404, etc.)
 * @returns Object with route info, or null if no internal link should be shown
 *
 * @example
 * getAnalyticsRoute('post', 42) // { to: '/content/$postId', params: { postId: '42' } }
 * getAnalyticsRoute('author', 1) // { to: '/author/$authorId', params: { authorId: '1' } }
 * getAnalyticsRoute('category', 5) // { to: '/category/$termId', params: { termId: '5' } }
 * getAnalyticsRoute('home', 0) // null (no resourceId)
 * getAnalyticsRoute('home', 0, [], 123) // { to: '/url/$resourceId', params: { resourceId: '123' } }
 */
export const getAnalyticsRoute = (
  pageType: string | undefined | null,
  pageWpId: number | string | undefined | null,
  taxonomies?: string[],
  resourceId?: number | string | null
): { to: string; params: Record<string, string> } | null => {
  // No link if missing required data
  if (!pageType || !pageWpId) {
    // For non-linkable types without a WP ID, use resourceId to link to URL report
    // Exclude 'unknown' — we don't know what the page is
    if (pageType && pageType !== 'unknown' && resourceId && NON_LINKABLE_TYPES.has(pageType)) {
      return { to: '/url/$resourceId', params: { resourceId: String(resourceId) } }
    }
    return null
  }

  const id = String(pageWpId)

  // Non-content pages: link to URL report if resourceId available, otherwise no link
  if (NON_LINKABLE_TYPES.has(pageType)) {
    if (resourceId && pageType !== 'unknown') {
      return { to: '/url/$resourceId', params: { resourceId: String(resourceId) } }
    }
    return null
  }

  // Author pages (stored as 'author' in resources table)
  if (pageType === 'author') {
    return { to: '/author/$authorId', params: { authorId: id } }
  }

  // Built-in taxonomy types
  if (TAXONOMY_TYPES.has(pageType)) {
    return { to: '/category/$termId', params: { termId: id } }
  }

  // Custom taxonomy detection (if taxonomy list provided)
  if (taxonomies?.includes(pageType)) {
    return { to: '/category/$termId', params: { termId: id } }
  }

  // Default: treat as content (post, page, product, etc.)
  return { to: '/content/$postId', params: { postId: id } }
}
