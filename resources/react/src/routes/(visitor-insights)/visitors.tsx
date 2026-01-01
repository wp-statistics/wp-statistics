import { createFileRoute } from '@tanstack/react-router'

import {
  searchValidators,
  type BaseSearchParams,
  type UrlFilter,
} from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }
export type VisitorsSearchParams = BaseSearchParams

export const Route = createFileRoute('/(visitor-insights)/visitors')({
  validateSearch: searchValidators.withPage,
})
