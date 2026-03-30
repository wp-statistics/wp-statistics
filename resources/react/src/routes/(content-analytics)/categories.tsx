import { createFileRoute } from '@tanstack/react-router'

import { searchValidators } from '@/lib/route-validation'

/**
 * Categories Overview page route
 *
 * URL: /categories
 * Shows taxonomy-level metrics and top terms/content/authors analytics.
 */
export const Route = createFileRoute('/(content-analytics)/categories')({
  validateSearch: searchValidators.filtersOnly,
})
