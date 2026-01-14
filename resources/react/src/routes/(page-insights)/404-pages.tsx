import { createFileRoute } from '@tanstack/react-router'

import { searchValidators, type BaseSearchParams } from '@/lib/route-validation'

export type NotFoundPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/404-pages')({
  validateSearch: searchValidators.withPage,
})
