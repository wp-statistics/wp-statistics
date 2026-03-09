declare global {
  // Filter operator types
  type FilterOperator =
    | 'is'
    | 'is_not'
    | 'is_null'
    | 'in'
    | 'not_in'
    | 'contains'
    | 'starts_with'
    | 'ends_with'
    | 'gt'
    | 'gte'
    | 'lt'
    | 'lte'
    | 'between'
    | 'before'
    | 'after'
    | 'in_the_last'

  // Input types for filter fields
  type FilterInputType = 'text' | 'number' | 'dropdown' | 'searchable' | 'date'

  // Operator value types
  type OperatorType = 'single' | 'multiple' | 'range'

  // Filter groups
  type FilterGroup = 'visitors' | 'views' | 'content' | 'geographic' | 'referrals' | 'devices' | 'individual-content' | 'categories' | 'individual-category' | 'individual-author'

  // Dropdown option
  interface FilterOption<T extends string | number = string | number> {
    value: T
    label: string
  }

  // Operator definition
  interface FilterOperatorDefinition {
    label: string
    type: OperatorType
  }

  // Base filter field without options
  interface BaseFilterField<
    TName extends string,
    TOperators extends FilterOperator[],
    TInputType extends FilterInputType,
    TGroups extends FilterGroup[] | [],
  > {
    name: TName
    label: string
    supportedOperators: TOperators
    inputType: TInputType
    groups: TGroups
  }

  // Filter field with options (for dropdown type)
  interface FilterFieldWithOptions<
    TName extends string,
    TOperators extends FilterOperator[],
    TGroups extends FilterGroup[] | [],
    TOptionValue extends string | number = string,
  > extends BaseFilterField<TName, TOperators, 'dropdown', TGroups> {
    options: FilterOption<TOptionValue>[]
  }

  // Filter field name - string type to accept any field from PHP
  type FilterFieldName = string

  // Generic filter field type that accepts any field from wps_react.filters.fields
  interface FilterFieldDefinition {
    name: string
    label: string
    supportedOperators: FilterOperator[]
    inputType: FilterInputType
    groups: FilterGroup[]
    options?: FilterOption[]
  }

  // Filter fields map - dynamic from PHP via wps_react.filters.fields
  type FilterFields = Record<string, FilterFieldDefinition>

  // Filter operators map type
  interface FilterOperators {
    is: FilterOperatorDefinition & { type: 'single' }
    is_not: FilterOperatorDefinition & { type: 'single' }
    is_null: FilterOperatorDefinition & { type: 'single' }
    in: FilterOperatorDefinition & { type: 'multiple' }
    not_in: FilterOperatorDefinition & { type: 'multiple' }
    contains: FilterOperatorDefinition & { type: 'single' }
    starts_with: FilterOperatorDefinition & { type: 'single' }
    ends_with: FilterOperatorDefinition & { type: 'single' }
    gt: FilterOperatorDefinition & { type: 'single' }
    gte: FilterOperatorDefinition & { type: 'single' }
    lt: FilterOperatorDefinition & { type: 'single' }
    lte: FilterOperatorDefinition & { type: 'single' }
    between: FilterOperatorDefinition & { type: 'range' }
    before: FilterOperatorDefinition & { type: 'single' }
    after: FilterOperatorDefinition & { type: 'single' }
    in_the_last: FilterOperatorDefinition & { type: 'single' }
  }

  // Filters configuration type
  interface FiltersConfig {
    fields: FilterFields
    operators: FilterOperators
  }

  // URL filter format for persistence
  interface PersistedUrlFilter {
    field: string
    operator: string
    value: string | string[]
  }

  // Global filters preferences stored in user meta
  interface GlobalFiltersPreferences {
    date_from?: string
    date_to?: string
    previous_date_from?: string
    previous_date_to?: string
    /** Period preset name (e.g., 'yesterday', 'last30') for dynamic date resolution */
    period?: string
    filters?: PersistedUrlFilter[]
    updated_at?: string
  }

  // User preferences container
  interface UserPreferences {
    globalFilters?: GlobalFiltersPreferences | null
  }

  // Network site data
  interface NetworkSite {
    blogId: number
    name: string
    url: string
    dashboardUrl: string
  }

  // Network data configuration
  interface NetworkData {
    isMultisite: boolean
    isNetworkAdmin: boolean
    sites: NetworkSite[]
  }

  // Notice item data
  interface NoticeItem {
    id: string
    message: string
    type: 'info' | 'warning' | 'error' | 'success'
    dismissible: boolean
    actionUrl?: string | null
    actionLabel?: string | null
    helpUrl?: string | null
    priority: number
    pages?: string[] // Routes where notice should appear (empty = all pages)
  }

  // Notice data configuration
  interface NoticeData {
    items: NoticeItem[]
    dismissUrl: string
    nonce: string
  }

  // PHP-defined report column
  interface PhpReportColumn {
    key: string
    title: string
    type: 'text' | 'numeric' | 'page-link' | 'percentage' | 'duration' | 'location' | 'referrer' | 'computed-ratio' | 'source-category'
    priority?: 'primary' | 'secondary' | 'hidden'
    cardPosition?: 'header' | 'body' | 'footer'
    mobileLabel?: string
    sortable?: boolean
    comparable?: boolean
    previousKey?: string
    size?: string
    // For location type
    linkTo?: string
    linkParamField?: string
    // For computed-ratio type
    numerator?: string
    denominator?: string
    previousNumerator?: string
    previousDenominator?: string
    decimals?: number
  }

  // PHP-defined widget config
  interface PhpReportWidget {
    pageId: string
    id: string
    label: string
    queryId: string
    type: 'bar-list'
    labelField: string
    valueField: string
    previousValueField?: string
    link?: { title: string; to: string }
    columnHeaders?: { left: string; right: string }
  }

  // PHP-defined export config
  interface PhpReportExport {
    sources: string[]
    group_by: string[]
    context?: string
    columns?: string[]
  }

  // PHP-defined batch query item (sources/group_by optional for chart queries)
  interface PhpBatchQuery {
    id: string
    sources?: string[]
    group_by?: string[]
    format?: string
    columns?: string[]
    compare?: boolean
    [key: string]: unknown
  }

  // PHP-defined chart config (for chart-above-table reports)
  interface PhpChartConfig {
    queryId: string
    title: string
    compareMetricKey?: string
  }

  // Overview page icon types
  type OverviewIconType = 'browser' | 'os' | 'country' | 'device'

  // Overview metric definition (extracts a text value from a flat query result)
  interface PhpOverviewMetric {
    id: string
    label: string
    queryId: string
    valueField: string
  }

  // Overview map widget config
  interface PhpMapWidgetConfig {
    title: string
    metric?: string
    enableCityDrilldown?: boolean
    enableMetricToggle?: boolean
    availableMetrics?: Array<{ value: string; label: string }>
  }

  // Overview widget definition
  interface PhpOverviewWidget {
    id: string
    label: string
    type: 'metrics' | 'bar-list' | 'map'
    defaultSize: number
    queryId?: string
    labelField?: string
    valueField?: string
    iconType?: OverviewIconType
    iconSlugField?: string
    columnHeaders?: { left: string; right: string }
    link?: { to: string }
    linkTo?: string
    linkParamField?: string
    mapConfig?: PhpMapWidgetConfig
  }

  // Overview page definition
  interface PhpOverviewDefinition {
    type: 'overview'
    pageId: string
    title: string
    filterGroup: string
    hideFilters?: boolean
    showFilterButton?: boolean
    queries: PhpBatchQuery[]
    metrics: PhpOverviewMetric[]
    widgets: PhpOverviewWidget[]
  }

  // PHP-defined report definition
  interface PhpReportDefinition {
    type?: 'table'
    title: string
    context: string
    filterGroup: string
    dataSource: {
      sources?: string[]
      group_by?: string[]
      // Batch query support
      queryId?: string
      queries?: PhpBatchQuery[]
      // Column name mapping: frontend column ID → API sort field
      columnMapping?: Record<string, string>
    }
    columns: PhpReportColumn[]
    defaultSort?: { id: string; desc: boolean }
    perPage?: number
    emptyStateMessage?: string
    routeName?: string
    defaultHiddenColumns?: string[]
    customFilters?: string[]
    columnConfig?: {
      baseColumns: string[]
      columnDependencies: Record<string, string[]>
    }
    defaultApiColumns?: string[]
    hideFilters?: boolean
    chart?: PhpChartConfig
    widget?: PhpReportWidget
    export?: PhpReportExport
  }

  interface wpsReact {
    layout: {
      sidebar: Record<
        string,
        {
          icon: string
          label: string
          slug: string
          subPages?: Record<
            string,
            {
              label: string
              slug: string
            }
          >
        }
      >
    }
    globals: {
      isPremium: null | boolean
      ajaxUrl: string
      nonce: string
      pluginUrl: string
      siteUrl: string
      analyticsAction: string
      filterAction: string
      userPreferencesAction: string
      trackLoggedInUsers: boolean
      storeIp: boolean
      userIp: string
      accessLevel: 'none' | 'own_content' | 'view_stats' | 'view_all' | 'manage'
      userId: number
      userPreferences?: UserPreferences
      currentPage: string
      userCountry?: string
      userCountryName?: string
      dateFormat: string
      startOfWeek: number // 0 = Sunday, 1 = Monday, etc.
    }
    header: Record<
      string,
      {
        isActive: boolean
        items?: unknown[]
        url?: string
        icon: string
        label: string
      }
    >
    filters: FiltersConfig
    network: NetworkData
    notices?: NoticeData
    reports?: Record<string, PhpReportDefinition | PhpOverviewDefinition>
  }
}

export {}
