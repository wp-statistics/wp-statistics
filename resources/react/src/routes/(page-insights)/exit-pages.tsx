import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams, type UrlFilter } from '@/lib/route-validation'

export type { UrlFilter }
export type ExitPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/exit-pages')({
  validateSearch: searchValidators.withPage,
})
