import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators } from '@/lib/route-validation'

export type EntryPagesSearchParams = BaseSearchParams

export const Route = createFileRoute('/(page-insights)/entry-pages')({
  validateSearch: searchValidators.withPage,
})
