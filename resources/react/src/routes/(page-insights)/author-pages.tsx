import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams, type UrlFilter } from '@/lib/route-validation'

export type { UrlFilter }
export type AuthorPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/author-pages')({
  validateSearch: searchValidators.withPage,
})
