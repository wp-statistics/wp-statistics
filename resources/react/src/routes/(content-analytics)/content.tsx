import { createFileRoute } from '@tanstack/react-router'

import {
  type BaseSearchParams,
  searchValidators,
  type UrlFilter,
} from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }

/**
 * Content page search params
 * - resource_id: When provided, shows individual content view
 * - post_type: Filter by post type (default: 'post')
 */
export interface ContentSearchParams extends Omit<BaseSearchParams, 'page'> {
  resource_id?: number
  post_type?: string
}

/**
 * Custom validator that extends the base validator with content-specific params
 */
const validateSearch = (search: Record<string, unknown>): ContentSearchParams => {
  const baseParams = searchValidators.filtersOnly(search)

  return {
    ...baseParams,
    resource_id: typeof search.resource_id === 'number'
      ? search.resource_id
      : typeof search.resource_id === 'string'
        ? parseInt(search.resource_id, 10) || undefined
        : undefined,
    post_type: typeof search.post_type === 'string' ? search.post_type : undefined,
  }
}

export const Route = createFileRoute('/(content-analytics)/content')({
  validateSearch,
})
