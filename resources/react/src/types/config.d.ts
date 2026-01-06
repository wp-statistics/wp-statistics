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
  type FilterGroup = 'visitors' | 'views'

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
      hashIps: boolean
      userPreferences?: UserPreferences
      currentPage: string
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
  }
}

export {}
