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

  // All filter field names
  type FilterFieldName =
    | 'country'
    | 'continent'
    | 'city'
    | 'region'
    | 'browser'
    | 'browser_version'
    | 'os'
    | 'device_type'
    | 'resolution'
    | 'referrer'
    | 'referrer_type'
    | 'referrer_channel'
    | 'referrer_domain'
    | 'referrer_name'
    | 'post_type'
    | 'author'
    | 'page'
    | 'resource_id'
    | 'user_id'
    | 'logged_in'
    | 'ip'
    | 'user_role'
    | 'visitor_type'
    | 'session_duration'
    | 'views_per_session'
    | 'total_views'
    | 'total_sessions'
    | 'first_seen'
    | 'last_seen'
    | 'bounce'
    | 'language'
    | 'timezone'

  // Filter fields map type
  interface FilterFields {
    country: BaseFilterField<'country', ['is', 'is_not'], 'searchable', ['visitors']>
    continent: BaseFilterField<'continent', ['is', 'is_not', 'in', 'not_in'], 'text', []>
    city: BaseFilterField<'city', ['is', 'is_not'], 'searchable', ['visitors']>
    region: BaseFilterField<'region', ['is', 'is_not'], 'searchable', ['visitors']>
    browser: BaseFilterField<'browser', ['is', 'is_not'], 'searchable', ['visitors']>
    browser_version: BaseFilterField<
      'browser_version',
      ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'],
      'text',
      []
    >
    os: BaseFilterField<'os', ['is', 'is_not'], 'searchable', ['visitors']>
    device_type: FilterFieldWithOptions<'device_type', ['is', 'is_not'], ['visitors'], 'desktop' | 'mobile' | 'tablet'>
    resolution: BaseFilterField<'resolution', ['is', 'is_not'], 'searchable', ['visitors']>
    referrer: BaseFilterField<'referrer', ['is', 'is_not', 'contains'], 'searchable', ['visitors']>
    referrer_type: BaseFilterField<
      'referrer_type',
      ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'],
      'text',
      []
    >
    referrer_channel: FilterFieldWithOptions<
      'referrer_channel',
      ['is', 'is_not'],
      ['visitors'],
      'direct' | 'search' | 'social' | 'referral' | 'email' | 'paid'
    >
    referrer_domain: BaseFilterField<'referrer_domain', ['is', 'is_not', 'contains'], 'searchable', ['visitors']>
    referrer_name: BaseFilterField<
      'referrer_name',
      ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'],
      'text',
      []
    >
    post_type: FilterFieldWithOptions<'post_type', ['is', 'is_not'], ['views'], 'post' | 'page' | 'attachment'>
    author: BaseFilterField<'author', ['is', 'is_not'], 'searchable', ['views']>
    page: BaseFilterField<'page', ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'], 'text', []>
    resource_id: BaseFilterField<'resource_id', ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'], 'text', []>
    user_id: BaseFilterField<'user_id', ['is', 'is_not', 'is_null'], 'number', ['visitors']>
    logged_in: FilterFieldWithOptions<'logged_in', ['is'], ['visitors'], '1' | '0'>
    ip: BaseFilterField<'ip', ['is', 'is_not', 'contains'], 'text', ['visitors']>
    user_role: FilterFieldWithOptions<
      'user_role',
      ['is', 'is_not'],
      ['visitors'],
      'administrator' | 'editor' | 'author' | 'contributor' | 'subscriber' | 'customer' | 'shop_manager'
    >
    visitor_type: FilterFieldWithOptions<'visitor_type', ['is'], ['visitors'], 1 | 0>
    session_duration: BaseFilterField<'session_duration', ['gt', 'lt', 'between'], 'number', ['visitors']>
    views_per_session: BaseFilterField<'views_per_session', ['is', 'gt', 'lt'], 'number', ['visitors']>
    total_views: BaseFilterField<'total_views', ['gt', 'lt', 'between'], 'number', ['visitors']>
    total_sessions: BaseFilterField<'total_sessions', ['gt', 'lt', 'between'], 'number', ['visitors']>
    first_seen: BaseFilterField<'first_seen', ['between', 'before', 'after'], 'date', ['visitors']>
    last_seen: BaseFilterField<'last_seen', ['in_the_last', 'between', 'before', 'after'], 'date', ['visitors']>
    bounce: FilterFieldWithOptions<'bounce', ['is'], ['visitors'], '1' | '0'>
    language: BaseFilterField<'language', ['is', 'is_not'], 'searchable', ['visitors']>
    timezone: BaseFilterField<'timezone', ['is', 'is_not'], 'searchable', ['visitors']>
  }

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
      analyticsAction: 'wp_statistics_analytics'
      userPreferencesAction: 'wp_statistics_user_preferences'
      trackLoggedInUsers: boolean
      hashIps: boolean
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
  }
}

export {}
