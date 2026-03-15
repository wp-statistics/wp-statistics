import { createFileRoute } from '@tanstack/react-router'

import { searchValidators } from '@/lib/route-validation'

/**
 * Authors Overview page route
 *
 * URL: /authors
 * Shows author-level metrics and top authors by various metrics.
 */
export const Route = createFileRoute('/(content-analytics)/authors')({
  validateSearch: searchValidators.filtersOnly,
})
