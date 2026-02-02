import { createFileRoute } from '@tanstack/react-router'

import { createSearchValidator } from '@/lib/route-validation'

/**
 * Single UTM detail page route
 *
 * URL: /utm/{utmType}/{utmValue}
 * Shows analytics for a specific UTM parameter value (campaign, source, or medium).
 */
export const Route = createFileRoute('/(referrals)/utm_/$utmType/$utmValue')({
  validateSearch: createSearchValidator({ includePage: false }),
})
