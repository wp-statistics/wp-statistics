import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }

/**
 * Categories page search params
 * - term: When provided with taxonomy, shows individual category view
 * - taxonomy: Taxonomy type (category, post_tag, custom) - defaults to 'category'
 * - view: When 'table', shows the Top Categories Table view
 */
export interface CategoriesSearchParams extends Omit<BaseSearchParams, 'page'> {
  term?: number
  taxonomy?: string
  view?: 'table'
}

/**
 * Custom validator that extends the base validator with categories-specific params
 */
const validateSearch = (search: Record<string, unknown>): CategoriesSearchParams => {
  const baseParams = searchValidators.filtersOnly(search)

  return {
    ...baseParams,
    term:
      typeof search.term === 'number'
        ? search.term
        : typeof search.term === 'string'
          ? parseInt(search.term, 10) || undefined
          : undefined,
    taxonomy: typeof search.taxonomy === 'string' ? search.taxonomy : undefined,
    view: search.view === 'table' ? 'table' : undefined,
  }
}

export const Route = createFileRoute('/(content-analytics)/categories')({
  validateSearch,
})
