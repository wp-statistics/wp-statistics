import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }
export type PageInsightsOverviewSearchParams = Omit<BaseSearchParams, 'page'>

export const Route = createFileRoute('/(page-insights)/page-insights-overview')({
  validateSearch: searchValidators.filtersOnly,
})
