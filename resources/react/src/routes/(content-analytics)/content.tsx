import { createFileRoute } from '@tanstack/react-router'

import { searchValidators } from '@/lib/route-validation'

/**
 * Content Overview page route
 *
 * URL: /content
 * Shows aggregated content analytics across all content types.
 */
export const Route = createFileRoute('/(content-analytics)/content')({
  validateSearch: searchValidators.filtersOnly,
})
