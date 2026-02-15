import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators } from '@/lib/route-validation'

export type ExitPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/exit-pages')({
  validateSearch: searchValidators.withPage,
})
