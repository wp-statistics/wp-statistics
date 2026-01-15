import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams,searchValidators } from '@/lib/route-validation'

export type AuthorPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/author-pages')({
  validateSearch: searchValidators.withPage,
})
