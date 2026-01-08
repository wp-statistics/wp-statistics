import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }

/**
 * Individual category page search params
 * - term_id: Required - the taxonomy term to show analytics for
 */
export interface IndividualCategorySearchParams extends Omit<BaseSearchParams, 'page'> {
  term_id: number
}

/**
 * Custom validator that extends the base validator with individual-category-specific params
 */
const validateSearch = (search: Record<string, unknown>): IndividualCategorySearchParams => {
  const baseParams = searchValidators.filtersOnly(search)

  // Parse term_id (required)
  let termId: number | undefined
  if (typeof search.term_id === 'number') {
    termId = search.term_id
  } else if (typeof search.term_id === 'string') {
    termId = parseInt(search.term_id, 10) || undefined
  }

  return {
    ...baseParams,
    term_id: termId as number,
  }
}

export const Route = createFileRoute('/(content-analytics)/individual-category')({
  validateSearch,
})
