import { createFileRoute } from '@tanstack/react-router'

import { createSearchValidator } from '@/lib/route-validation'

/**
 * Single Author page route
 *
 * URL: /author/{authorId}
 * Shows analytics for a specific author's archive page.
 */
export const Route = createFileRoute('/(content-analytics)/author_/$authorId')({
  validateSearch: createSearchValidator({ includePage: false }),
})
