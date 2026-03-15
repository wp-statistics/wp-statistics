import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }
export type ReferredVisitorsSearchParams = BaseSearchParams

export const Route = createFileRoute('/(referrals)/referred-visitors')({
  validateSearch: searchValidators.withPage,
})
