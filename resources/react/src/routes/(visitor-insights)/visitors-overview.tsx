import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }
export type VisitorsOverviewSearchParams = Omit<BaseSearchParams, 'page'>

export const Route = createFileRoute('/(visitor-insights)/visitors-overview')({
  validateSearch: searchValidators.filtersOnly,
})
