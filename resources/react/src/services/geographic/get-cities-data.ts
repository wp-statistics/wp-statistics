import { clientRequest } from '@lib/client-request'
import { queryOptions } from '@tanstack/react-query'

export interface GetCitiesDataParams {
  countryCode: string
  metric?: 'visitors' | 'views'
  date_from?: string
  date_to?: string
}

export interface CityDataItem {
  city_id: number
  city_name: string
  city_region_code: string | null
  city_region_name: string
  city_country_id: number
  country_code: string
  country_name: string
  visitors?: number
  views?: number
}

export interface GetCitiesDataResponse {
  success: boolean
  data: {
    items: CityDataItem[]
    total?: number
  }
}

export const getCitiesDataQueryOptions = ({
  countryCode,
  metric = 'visitors',
  date_from,
  date_to,
}: GetCitiesDataParams) => {
  return queryOptions({
    queryKey: ['geographic', 'cities', countryCode, metric, date_from, date_to],
    queryFn: () =>
      clientRequest.post<GetCitiesDataResponse>('', {
        action: 'wp_statistics_analytics',
        sources: [metric],
        group_by: ['city'],
        filters: {
          country: {
            operator: 'is',
            value: countryCode,
          },
        },
        ...(date_from && { date_from }),
        ...(date_to && { date_to }),
      }),
    staleTime: 5 * 60 * 1000, // 5 minutes cache
  })
}
