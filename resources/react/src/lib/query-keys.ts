/**
 * Query Key Factory
 *
 * Provides a centralized, type-safe way to manage React Query keys.
 * Using a factory pattern ensures:
 * - Consistent key structure across the application
 * - Easy cache invalidation (invalidate all 'visitors' queries, etc.)
 * - Type safety for query key parameters
 * - Clear hierarchical organization
 *
 * @example
 * // Use in a query
 * useQuery(queryKeys.visitors.list({ dateFrom: '2024-01-01', dateTo: '2024-01-31' }))
 *
 * @example
 * // Invalidate all visitor queries
 * queryClient.invalidateQueries({ queryKey: queryKeys.visitors.all() })
 *
 * @example
 * // Invalidate specific query
 * queryClient.invalidateQueries({ queryKey: queryKeys.topPages.list({ page: 1, ... }) })
 */

import type { ComparisonMode } from '@/components/custom/date-range-picker'

import type { ApiFilters } from './api-filter-transform'

// ============================================================================
// Parameter Types
// ============================================================================

/** Base date range parameters for all date-filtered queries */
export interface DateParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  comparisonMode?: ComparisonMode
}

/** Pagination parameters */
export interface PaginationParams {
  page: number
  perPage: number
}

/** Sorting parameters */
export interface SortParams {
  orderBy: string
  order: 'asc' | 'desc'
}

/** Filter parameters */
export interface FilterParams {
  filters?: ApiFilters
}

/** Query-level filter for batch queries (e.g., continent filter) */
export interface QueryFilter {
  key: string
  operator: string
  value: string | string[]
}

/** Combined paginated, sorted, filtered date query params */
export interface ListQueryParams extends DateParams, PaginationParams, SortParams, FilterParams {
  context?: string
  columns?: string[]
  queryFilters?: QueryFilter[]
}

/** Timeframe parameter for chart data */
export interface TimeframeParams {
  timeframe?: string
}

// ============================================================================
// Query Key Factory
// ============================================================================

/**
 * Centralized query key factory for all React Query keys.
 * Organized by domain/feature area.
 */
export const queryKeys = {
  /** Root key for all WP Statistics queries */
  all: ['wp-statistics'] as const,

  // ==========================================================================
  // Page Insights
  // ==========================================================================
  pageInsights: {
    all: () => [...queryKeys.all, 'page-insights'] as const,

    /** Overview statistics for page insights */
    overview: (params: DateParams & FilterParams) =>
      [...queryKeys.pageInsights.all(), 'overview', params] as const,

    /** Top pages list (paginated) */
    topPages: (params: ListQueryParams) =>
      [...queryKeys.pageInsights.all(), 'top-pages', params] as const,

    /** 404 pages list */
    notFound: (params: DateParams & PaginationParams) =>
      [...queryKeys.pageInsights.all(), '404', params] as const,

    /** Category pages list */
    categoryPages: (params: ListQueryParams & { categoryId: number }) =>
      [...queryKeys.pageInsights.all(), 'category-pages', params] as const,

    /** Author pages list */
    authorPages: (params: ListQueryParams & { authorId: number }) =>
      [...queryKeys.pageInsights.all(), 'author-pages', params] as const,
  },

  // ==========================================================================
  // Visitors
  // ==========================================================================
  visitors: {
    all: () => [...queryKeys.all, 'visitors'] as const,

    /** Visitor overview statistics */
    overview: (params: DateParams & FilterParams & TimeframeParams) =>
      [...queryKeys.visitors.all(), 'overview', params] as const,

    /** Visitors list (paginated) */
    list: (params: ListQueryParams) =>
      [...queryKeys.visitors.all(), 'list', params] as const,

    /** Top visitors */
    top: (params: ListQueryParams) =>
      [...queryKeys.visitors.all(), 'top', params] as const,

    /** Online visitors */
    online: (params: PaginationParams & SortParams & { timeRangeMinutes?: number; context?: string; columns?: string[] }) =>
      [...queryKeys.visitors.all(), 'online', params] as const,

    /** Views list */
    views: (params: ListQueryParams) =>
      [...queryKeys.visitors.all(), 'views', params] as const,

    /** Logged in users */
    loggedIn: (params: ListQueryParams) =>
      [...queryKeys.visitors.all(), 'logged-in', params] as const,

    /** Traffic trends */
    trafficTrends: () =>
      [...queryKeys.visitors.all(), 'traffic-trends'] as const,

    /** Global visitor distribution */
    globalDistribution: () =>
      [...queryKeys.visitors.all(), 'global-distribution'] as const,
  },

  // ==========================================================================
  // Referrals
  // ==========================================================================
  referrals: {
    all: () => [...queryKeys.all, 'referrals'] as const,

    /** Referrals overview */
    overview: (params: DateParams & FilterParams & TimeframeParams) =>
      [...queryKeys.referrals.all(), 'overview', params] as const,

    /** Referrers list */
    list: (params: ListQueryParams) =>
      [...queryKeys.referrals.all(), 'list', params] as const,

    /** Search engines */
    searchEngines: (params: ListQueryParams) =>
      [...queryKeys.referrals.all(), 'search-engines', params] as const,

    /** Social media */
    socialMedia: (params: ListQueryParams) =>
      [...queryKeys.referrals.all(), 'social-media', params] as const,

    /** Source categories */
    sourceCategories: (params: ListQueryParams) =>
      [...queryKeys.referrals.all(), 'source-categories', params] as const,
  },

  // ==========================================================================
  // Geographic
  // ==========================================================================
  geographic: {
    all: () => [...queryKeys.all, 'geographic'] as const,

    /** Geographic overview */
    overview: (params: DateParams & FilterParams & { userCountry?: string }) =>
      [...queryKeys.geographic.all(), 'overview', params] as const,

    /** Regions by country */
    regionsByCountry: (params: DateParams & { countryCode: string; sources?: string[] }) =>
      [...queryKeys.geographic.all(), 'regions-by-country', params] as const,

    /** Cities data */
    cities: (params: { countryCode: string; metric: string; dateFrom: string; dateTo: string }) =>
      [...queryKeys.geographic.all(), 'cities', params] as const,

    /** Top countries widget */
    topCountries: () =>
      [...queryKeys.geographic.all(), 'top-countries'] as const,

    /** Countries list (paginated) */
    countries: (params: ListQueryParams) =>
      [...queryKeys.geographic.all(), 'countries', params] as const,

    /** Cities list (paginated) */
    citiesList: (params: ListQueryParams) =>
      [...queryKeys.geographic.all(), 'cities-list', params] as const,
  },

  // ==========================================================================
  // Content Analytics
  // ==========================================================================
  content: {
    all: () => [...queryKeys.all, 'content'] as const,

    /** Content overview */
    overview: (params: DateParams & FilterParams & TimeframeParams) =>
      [...queryKeys.content.all(), 'overview', params] as const,

    /** Individual content item */
    detail: (params: ListQueryParams & { contentId: number | string }) =>
      [...queryKeys.content.all(), 'detail', params] as const,

    /** Authors overview */
    authorsOverview: (params: DateParams & FilterParams) =>
      [...queryKeys.content.all(), 'authors-overview', params] as const,

    /** Top authors */
    topAuthors: (params: ListQueryParams) =>
      [...queryKeys.content.all(), 'top-authors', params] as const,

    /** Individual author */
    author: (params: ListQueryParams & { authorId: number }) =>
      [...queryKeys.content.all(), 'author', params] as const,

    /** Categories overview */
    categoriesOverview: (params: DateParams & FilterParams & TimeframeParams) =>
      [...queryKeys.content.all(), 'categories-overview', params] as const,

    /** Top categories */
    topCategories: (params: ListQueryParams) =>
      [...queryKeys.content.all(), 'top-categories', params] as const,

    /** Individual category */
    category: (params: ListQueryParams & { categoryId: number }) =>
      [...queryKeys.content.all(), 'category', params] as const,
  },

  // ==========================================================================
  // Devices
  // ==========================================================================
  devices: {
    all: () => [...queryKeys.all, 'devices'] as const,

    /** Devices overview */
    overview: (params: DateParams & FilterParams) =>
      [...queryKeys.devices.all(), 'overview', params] as const,

    /** Devices type widget */
    types: () =>
      [...queryKeys.devices.all(), 'types'] as const,

    /** Operating systems widget */
    os: () =>
      [...queryKeys.devices.all(), 'os'] as const,
  },

  // ==========================================================================
  // Network
  // ==========================================================================
  network: {
    all: () => [...queryKeys.all, 'network'] as const,

    /** Network stats */
    stats: (params: DateParams) =>
      [...queryKeys.network.all(), 'stats', params] as const,
  },

  // ==========================================================================
  // Search
  // ==========================================================================
  search: {
    all: () => [...queryKeys.all, 'search'] as const,

    /** Search terms */
    terms: (params: DateParams & PaginationParams) =>
      [...queryKeys.search.all(), 'terms', params] as const,
  },

  // ==========================================================================
  // Filters
  // ==========================================================================
  filters: {
    all: () => [...queryKeys.all, 'filters'] as const,

    /** Searchable filter options */
    options: (params: { filter: string; search?: string; limit?: number }) =>
      [...queryKeys.filters.all(), 'options', params] as const,
  },
} as const

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Creates a standard date params object from individual values.
 * Useful for creating consistent query key params.
 */
export function createDateParams(
  dateFrom: string,
  dateTo: string,
  compareDateFrom?: string,
  compareDateTo?: string,
  comparisonMode?: ComparisonMode
): DateParams {
  return {
    dateFrom,
    dateTo,
    ...(compareDateFrom && { compareDateFrom }),
    ...(compareDateTo && { compareDateTo }),
    ...(comparisonMode && { comparisonMode }),
  }
}

/**
 * Creates standard list query params from individual values.
 */
export function createListParams(
  dateFrom: string,
  dateTo: string,
  page: number,
  perPage: number,
  orderBy: string,
  order: 'asc' | 'desc',
  options?: {
    compareDateFrom?: string
    compareDateTo?: string
    comparisonMode?: ComparisonMode
    filters?: ApiFilters
    context?: string
    columns?: string[]
    queryFilters?: QueryFilter[]
  }
): ListQueryParams {
  return {
    dateFrom,
    dateTo,
    page,
    perPage,
    orderBy,
    order,
    ...(options?.compareDateFrom && { compareDateFrom: options.compareDateFrom }),
    ...(options?.compareDateTo && { compareDateTo: options.compareDateTo }),
    ...(options?.comparisonMode && { comparisonMode: options.comparisonMode }),
    ...(options?.filters && Object.keys(options.filters).length > 0 && { filters: options.filters }),
    ...(options?.context && { context: options.context }),
    ...(options?.columns && options.columns.length > 0 && { columns: options.columns }),
    ...(options?.queryFilters && options.queryFilters.length > 0 && { queryFilters: options.queryFilters }),
  }
}
