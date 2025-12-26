import { clientRequest } from '@lib/client-request'
import { queryOptions } from '@tanstack/react-query'

export interface GetRegionsByCountryParams {
  countryCode: string
  dateFrom: string
  dateTo: string
  sources?: ('visitors' | 'views')[]
}

export interface RegionItem {
  region_name: string
  region_code: string
  country_id?: number
  country_code: string
  country_name: string
  visitors?: number
  views?: number
}

export interface GetRegionsByCountryResponse {
  success: boolean
  data: {
    rows: RegionItem[]
    totals?: {
      visitors?: number
      views?: number
    }
  }
  meta?: {
    total_rows?: number
    page?: number
    per_page?: number
  }
}

export const getRegionsByCountryQueryOptions = ({
  countryCode,
  dateFrom,
  dateTo,
  sources = ['visitors'],
}: GetRegionsByCountryParams) => {
  return queryOptions({
    queryKey: ['geographic', 'regions-by-country', countryCode, dateFrom, dateTo, sources],
    queryFn: () =>
      clientRequest.post<GetRegionsByCountryResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          sources,
          group_by: ['region'],
          columns: ['region_name', 'region_code', 'country_code', 'country_name', ...sources],
          filters: {
            country: {
              is: countryCode,
            },
          },
          per_page: 100,
          order_by: sources[0] || 'visitors',
          order: 'DESC',
          format: 'table',
          show_totals: true,
        },
        {
          params: {
            action: 'wp_statistics_analytics',
          },
        }
      ),
    staleTime: 5 * 60 * 1000, // 5 minutes cache
    enabled: !!countryCode, // Only fetch when countryCode is provided
  })
}
