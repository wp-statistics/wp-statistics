import { createFileRoute } from '@tanstack/react-router'

import { createSearchValidator } from '@/lib/route-validation'

/**
 * Single URL page route
 *
 * URL: /url/{resourceId}
 * For pages without WordPress post IDs (home, search, 404, archives, etc.)
 * Uses the resources table PK as the identifier.
 */
export const Route = createFileRoute('/(page-insights)/url_/$resourceId')({
  validateSearch: createSearchValidator({ includePage: false }),
})
