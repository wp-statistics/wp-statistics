import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams, type UrlFilter } from '@/lib/route-validation'

export type { UrlFilter }
export type Pages404SearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/404-pages')({
  validateSearch: searchValidators.withPage,
})
