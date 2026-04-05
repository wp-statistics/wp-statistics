import { createFileRoute } from '@tanstack/react-router'

import { type BaseSearchParams, searchValidators, type UrlFilter } from '@/lib/route-validation'

// Re-export types for backward compatibility
export type { UrlFilter }
export type LoggedInUsersSearchParams = BaseSearchParams

export const Route = createFileRoute('/(visitor-insights)/logged-in-users')({
  validateSearch: searchValidators.withPage,
})
