import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }

/**
 * Individual content page search params
 * - resource_id: Required - the content item to show analytics for
 */
export interface IndividualContentSearchParams extends Omit<BaseSearchParams, 'page'> {
  resource_id: number
}

/**
 * Custom validator that extends the base validator with individual-content-specific params
 */
const validateSearch = (search: Record<string, unknown>): IndividualContentSearchParams => {
  const baseParams = searchValidators.filtersOnly(search)

  // Parse resource_id (required)
  let resourceId: number | undefined
  if (typeof search.resource_id === 'number') {
    resourceId = search.resource_id
  } else if (typeof search.resource_id === 'string') {
    resourceId = parseInt(search.resource_id, 10) || undefined
  }

  return {
    ...baseParams,
    resource_id: resourceId as number,
  }
}

export const Route = createFileRoute('/(content-analytics)/individual-content')({
  validateSearch,
})
