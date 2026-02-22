import { createFileRoute } from '@tanstack/react-router'

import { createSearchValidator } from '@/lib/route-validation'

/**
 * Single Visitor page route
 *
 * URL: /visitor/{type}/{id}
 * - type: 'user' | 'ip' | 'hash'
 * - id: WordPress user ID, IP address, or visitor hash
 *
 * Shows detailed analytics for a specific visitor.
 */
export const Route = createFileRoute('/(visitor-insights)/visitor_/$type/$id')({
  validateSearch: createSearchValidator({ includePage: true }),
})
