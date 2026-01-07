import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams, type UrlFilter } from '@/lib/route-validation'

export type { UrlFilter }
export type CategoryPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/category-pages')({
  validateSearch: searchValidators.withPage,
})
