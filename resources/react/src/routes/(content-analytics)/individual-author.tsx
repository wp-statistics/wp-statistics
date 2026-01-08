import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }

/**
 * Individual author page search params
 * - author_id: Required - the author to show analytics for
 */
export interface IndividualAuthorSearchParams extends Omit<BaseSearchParams, 'page'> {
  author_id: number
}

/**
 * Custom validator that extends the base validator with individual-author-specific params
 */
const validateSearch = (search: Record<string, unknown>): IndividualAuthorSearchParams => {
  const baseParams = searchValidators.filtersOnly(search)

  // Parse author_id (required)
  let authorId: number | undefined
  if (typeof search.author_id === 'number') {
    authorId = search.author_id
  } else if (typeof search.author_id === 'string') {
    authorId = parseInt(search.author_id, 10) || undefined
  }

  return {
    ...baseParams,
    author_id: authorId as number,
  }
}

export const Route = createFileRoute('/(content-analytics)/individual-author')({
  validateSearch,
})
