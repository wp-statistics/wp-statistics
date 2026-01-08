import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }

/**
 * Authors page search params
 * - post_type: Filter by post type (default: 'post')
 */
export interface AuthorsSearchParams extends Omit<BaseSearchParams, 'page'> {
  post_type?: string
}

/**
 * Custom validator that extends the base validator with authors-specific params
 */
const validateSearch = (search: Record<string, unknown>): AuthorsSearchParams => {
  const baseParams = searchValidators.filtersOnly(search)

  return {
    ...baseParams,
    post_type: typeof search.post_type === 'string' ? search.post_type : undefined,
  }
}

export const Route = createFileRoute('/(content-analytics)/authors')({
  validateSearch,
})
