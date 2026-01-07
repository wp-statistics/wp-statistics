import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }
export type TopPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/top-pages')({
  validateSearch: searchValidators.withPage,
})
