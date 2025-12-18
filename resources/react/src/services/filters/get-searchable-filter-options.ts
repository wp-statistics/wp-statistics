import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

// Response types for filter options API
export interface FilterOptionItem {
  value: string
  label: string
}

export interface FilterOptionsResponse {
  success: boolean
  data: {
    success: boolean
    options: FilterOptionItem[]
  }
}

// Request parameters for filter options
export interface FilterOptionsParams {
  filter: FilterFieldName
  search?: string
  limit?: number
}

/**
 * Query options for fetching searchable filter options
 * Used for searchable filter fields to load options dynamically via API
 *
 * @param params - The filter parameters
 * @param params.filter - The filter field name (e.g., 'country', 'browser', 'os')
 * @param params.search - Optional search term for filtering options
 * @param params.limit - Optional limit for number of results (defaults to 20)
 */
export const getSearchableFilterOptionsQueryOptions = (params: FilterOptionsParams) => {
  return queryOptions({
    queryKey: ['filter-options', params.filter, params.search, params.limit],
    queryFn: () =>
      clientRequest.post<FilterOptionsResponse>(
        '',
        {
          filter: params.filter,
          search: params.search,
          limit: params.limit,
        },
        {
          params: {
            action: 'wp_statistics_get_filter_options',
          },
        }
      ),
    enabled: !!params.filter,
    staleTime: 5 * 60 * 1000,
    placeholderData: (previousData) => previousData,
  })
}
