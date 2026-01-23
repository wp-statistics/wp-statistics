import { createFileRoute } from '@tanstack/react-router'

import { createSearchValidator } from '@/lib/route-validation'

/**
 * Single Category/Term page route
 *
 * URL: /category/{termId}
 * Shows analytics for a specific category or taxonomy term archive page.
 */
export const Route = createFileRoute('/(content-analytics)/category_/$termId')({
  validateSearch: createSearchValidator({ includePage: false }),
})
