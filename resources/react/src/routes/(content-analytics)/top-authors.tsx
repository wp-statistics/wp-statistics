import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }
export type TopAuthorsSearchParams = BaseSearchParams

export const Route = createFileRoute('/(content-analytics)/top-authors')({
  validateSearch: searchValidators.withPage,
})
