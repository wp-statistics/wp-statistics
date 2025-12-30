/**
 * Hook to consume the GlobalFiltersContext
 *
 * This hook provides access to global date range and filter state,
 * which persists across page navigation using a hybrid URL + User Preferences approach.
 *
 * @example
 * ```tsx
 * const {
 *   dateFrom,
 *   dateTo,
 *   compareDateFrom,
 *   compareDateTo,
 *   filters,
 *   page,
 *   isInitialized,
 *   setDateRange,
 *   setFilters,
 *   setPage,
 *   removeFilter,
 *   resetAll,
 *   apiDateParams,
 * } = useGlobalFilters()
 *
 * // Use apiDateParams directly in API queries
 * const { data } = useQuery({
 *   ...queryOptions,
 *   queryKey: ['data', apiDateParams, filters],
 *   enabled: isInitialized,
 * })
 * ```
 */

import { useContext } from 'react'

import { GlobalFiltersContext, type GlobalFiltersContextValue } from '@/contexts/global-filters-context'

export function useGlobalFilters(): GlobalFiltersContextValue {
  const context = useContext(GlobalFiltersContext)

  if (context === undefined) {
    throw new Error('useGlobalFilters must be used within a GlobalFiltersProvider')
  }

  return context
}
