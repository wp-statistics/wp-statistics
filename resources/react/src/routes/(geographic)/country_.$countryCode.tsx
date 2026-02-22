import { createFileRoute } from '@tanstack/react-router'

import { createSearchValidator } from '@/lib/route-validation'

/**
 * Single Country page route
 *
 * URL: /country/{countryCode}
 * Shows analytics for a specific country.
 */
export const Route = createFileRoute('/(geographic)/country_/$countryCode')({
  validateSearch: createSearchValidator({ includePage: false }),
})
