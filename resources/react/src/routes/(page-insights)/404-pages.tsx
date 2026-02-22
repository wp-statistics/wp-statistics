import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams,searchValidators } from '@/lib/route-validation'

export type NotFoundPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/404-pages')({
  validateSearch: searchValidators.withPage,
})
