import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams } from '@/lib/route-validation'

export type AuthorPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/author-pages')({
  validateSearch: searchValidators.withPage,
})
