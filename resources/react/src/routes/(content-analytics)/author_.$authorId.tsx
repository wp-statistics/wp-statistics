import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams,createSearchValidator } from '@/lib/route-validation'

/**
 * Search params for single author page
 */
export interface SingleAuthorSearchParams extends BaseSearchParams {
  post_type?: string
}

/**
 * Parses post_type from search params
 * Only allows alphanumeric and underscore (valid post type slugs)
 */
const parsePostType = (postType: unknown): string | undefined => {
  if (typeof postType !== 'string') return undefined
  // Only allow alphanumeric, underscore, and hyphen (valid post type slugs)
  if (!/^[a-zA-Z][a-zA-Z0-9_-]*$/.test(postType)) return undefined
  return postType
}

/**
 * Single Author page route
 *
 * URL: /author/{authorId}
 * Shows analytics for a specific author's archive page.
 */
export const Route = createFileRoute('/(content-analytics)/author_/$authorId')({
  validateSearch: (search: Record<string, unknown>): SingleAuthorSearchParams => {
    // Use base validator for common params (filters, dates, sorting)
    const baseResult = createSearchValidator({ includePage: false })(search)

    // Add post_type parsing
    const postType = parsePostType(search.post_type)

    return {
      ...baseResult,
      ...(postType && { post_type: postType }),
    }
  },
})
