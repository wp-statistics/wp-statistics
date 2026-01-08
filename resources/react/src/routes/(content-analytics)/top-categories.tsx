import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }

/**
 * Top Categories page search params
 * - taxonomy: Taxonomy type (category, post_tag, custom) - defaults to 'category'
 */
export interface TopCategoriesSearchParams extends BaseSearchParams {
  taxonomy?: string
}

/**
 * Cleans a taxonomy value that might be corrupted by WordPress query param interference.
 * WordPress's "?page=wp-statistics" can get appended to hash router search params on page reload.
 *
 * @example
 * cleanTaxonomyValue('post_tag') // 'post_tag'
 * cleanTaxonomyValue('post_tag?page=wp-statistics') // 'post_tag'
 * cleanTaxonomyValue('category&foo=bar') // 'category'
 */
const cleanTaxonomyValue = (value: string): string => {
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
 * Custom validator that extends the base validator with taxonomy param
 */
const validateSearch = (search: Record<string, unknown>): TopCategoriesSearchParams => {
  const baseParams = searchValidators.withPage(search)

  // Clean taxonomy value to handle WordPress query param interference on page reload
  let taxonomy: string | undefined
  if (typeof search.taxonomy === 'string' && search.taxonomy.trim() !== '') {
    taxonomy = cleanTaxonomyValue(search.taxonomy.trim())
  }

  return {
    ...baseParams,
    taxonomy,
  }
}

export const Route = createFileRoute('/(content-analytics)/top-categories')({
  validateSearch,
})
