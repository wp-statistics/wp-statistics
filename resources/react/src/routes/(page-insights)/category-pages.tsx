import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams,searchValidators } from '@/lib/route-validation'

export type CategoryPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/category-pages')({
  validateSearch: searchValidators.withPage,
})
