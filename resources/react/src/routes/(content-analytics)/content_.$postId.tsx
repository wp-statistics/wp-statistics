import { createFileRoute } from '@tanstack/react-router'

import { createSearchValidator } from '@/lib/route-validation'

/**
 * Single Content page route
 *
 * URL: /content/{postId}
 * No post_type qualifier needed - we filter by all queryable post types automatically.
 * The actual post type is determined from the response's page_type field.
 */
export const Route = createFileRoute('/(content-analytics)/content_/$postId')({
  validateSearch: createSearchValidator({ includePage: false }),
})
