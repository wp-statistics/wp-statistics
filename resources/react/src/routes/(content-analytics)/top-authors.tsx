import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }

/**
 * Top Authors page search params
 * - post_type: Filter by post type (default: 'post')
 */
export interface TopAuthorsSearchParams extends BaseSearchParams {
  post_type?: string
}

/**
 * Cleans a post_type value that might be corrupted by WordPress query param interference.
 * WordPress's "?page=wp-statistics" can get appended to hash router search params on page reload.
 *
 * @example
 * cleanPostTypeValue('post') // 'post'
 * cleanPostTypeValue('post?page=wp-statistics') // 'post'
 * cleanPostTypeValue('page&foo=bar') // 'page'
 */
const cleanPostTypeValue = (value: string): string => {
  // Remove any query string that got accidentally appended (from ?page=wp-statistics interference)
  const questionMarkIndex = value.indexOf('?')
  if (questionMarkIndex !== -1) {
    return value.substring(0, questionMarkIndex)
  }

  // Also handle & character in case params got concatenated
  const ampersandIndex = value.indexOf('&')
  if (ampersandIndex !== -1) {
    return value.substring(0, ampersandIndex)
  }

  return value
}

/**
 * Custom validator that extends the base validator with post_type param
 */
const validateSearch = (search: Record<string, unknown>): TopAuthorsSearchParams => {
  const baseParams = searchValidators.withPage(search)

  // Clean post_type value to handle WordPress query param interference on page reload
  let post_type: string | undefined
  if (typeof search.post_type === 'string' && search.post_type.trim() !== '') {
    post_type = cleanPostTypeValue(search.post_type.trim())
  }

  return {
    ...baseParams,
    post_type,
  }
}

export const Route = createFileRoute('/(content-analytics)/top-authors')({
  validateSearch,
})
