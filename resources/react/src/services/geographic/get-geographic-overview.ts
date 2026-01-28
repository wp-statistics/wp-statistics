import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Row types for geographic data
export interface CountryRow {
  country_code: string
  country_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface CityRow {
  city_name: string
  country_code: string
  country_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface RegionRow {
  region_name: string
  country_code: string
  country_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface ContinentRow {
  continent: string
  continent_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface CountriesMapRow {
  country_code: string
  country_name: string
  visitors: number | string
  views: number | string
}

// Table format response wrapper
interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: Record<string, unknown>
  }
  meta?: Record<string, unknown>
}

// Flat format response for single-value metrics
interface FlatQueryResult {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: Record<string, unknown>
}

// Batch response structure
export interface GeographicOverviewResponse {
  success: boolean
  items: {
    // Metrics for top values
    metrics_top_country?: FlatQueryResult
    metrics_top_region?: FlatQueryResult
    metrics_top_city?: FlatQueryResult
    // Map data
    countries_map?: TableQueryResult<CountriesMapRow>
    // Tables
    top_countries?: TableQueryResult<CountryRow>
    top_cities?: TableQueryResult<CityRow>
    top_european_countries?: TableQueryResult<CountryRow>
    top_regions?: TableQueryResult<RegionRow>
    top_us_states?: TableQueryResult<RegionRow>
    visitors_by_continent?: TableQueryResult<ContinentRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetGeographicOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
}

// European country codes for filtering
const EUROPEAN_COUNTRY_CODES = [
  'AL', 'AD', 'AT', 'BY', 'BE', 'BA', 'BG', 'HR', 'CY', 'CZ',
  'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IS', 'IE', 'IT',
  'XK', 'LV', 'LI', 'LT', 'LU', 'MT', 'MD', 'MC', 'ME', 'NL',
  'MK', 'NO', 'PL', 'PT', 'RO', 'RU', 'SM', 'RS', 'SK', 'SI',
  'ES', 'SE', 'CH', 'UA', 'GB', 'VA',
]

export const getGeographicOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters = [],
}: GetGeographicOverviewParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(compareDateFrom && compareDateTo)
  const wp = WordPress.getInstance()
  const userCountry = wp.getUserCountry() || 'US'

  return queryOptions({
    queryKey: ['geographic-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, userCountry, hasCompare],
    queryFn: () =>
      clientRequest.post<GeographicOverviewResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          queries: [
            // Metric: Top Country
            {
              id: 'metrics_top_country',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Metric: Top Region
            {
              id: 'metrics_top_region',
              sources: ['visitors'],
              group_by: ['region'],
              columns: ['region_name', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Metric: Top City
            {
              id: 'metrics_top_city',
              sources: ['visitors'],
              group_by: ['city'],
              columns: ['city_name', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Countries Map: All countries for GlobalMap
            {
              id: 'countries_map',
              sources: ['visitors', 'views'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors', 'views'],
              per_page: 250,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: false,
            },
            // Top Countries (5)
            {
              id: 'top_countries',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
            },
            // Top Cities (5)
            {
              id: 'top_cities',
              sources: ['visitors'],
              group_by: ['city'],
              columns: ['city_name', 'country_code', 'country_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
            },
            // Top European Countries (5)
            {
              id: 'top_european_countries',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors'],
              filters: [
                {
                  key: 'country',
                  operator: 'in',
                  value: EUROPEAN_COUNTRY_CODES,
                },
              ],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
            },
            // Top Regions of user's country (5) - skip if US
            ...(userCountry !== 'US'
              ? [
                  {
                    id: 'top_regions',
                    sources: ['visitors'],
                    group_by: ['region'],
                    columns: ['region_name', 'country_code', 'country_name', 'visitors'],
                    filters: [
                      {
                        key: 'country',
                        operator: 'is',
                        value: userCountry,
                      },
                    ],
                    per_page: 5,
                    order_by: 'visitors',
                    order: 'DESC',
                    format: 'table',
                    show_totals: true,
                    compare: hasCompare,
                  },
                ]
              : []),
            // Top US States (5)
            {
              id: 'top_us_states',
              sources: ['visitors'],
              group_by: ['region'],
              columns: ['region_name', 'country_code', 'country_name', 'visitors'],
              filters: [
                {
                  key: 'country',
                  operator: 'is',
                  value: 'US',
                },
              ],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
            },
            // Visitors by Continent
            {
              id: 'visitors_by_continent',
              sources: ['visitors'],
              group_by: ['continent'],
              columns: ['continent', 'continent_name', 'visitors'],
              per_page: 7, // All continents
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
            },
          ],
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
