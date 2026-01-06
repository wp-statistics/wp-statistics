type SearchableFilterFieldName =
  | 'country'
  | 'city'
  | 'continent'
  | 'region'
  | 'browser'
  | 'browser_version'
  | 'os'
  | 'referrer'
  | 'author'
  | 'timezone'
  | 'resolution'
  | 'language'
  | 'referrer_domain'
  | 'referrer_name'
  | 'resolution'
  | 'resource_id'

// Response types for filter options API
interface FilterOptionItem {
  value: string
  label: string
}

interface FilterOptionsResponse {
  success: boolean
  data: {
    success: boolean
    options: FilterOptionItem[]
  }
}

// Request parameters for filter options
interface FilterOptionsParams {
  filter: SearchableFilterFieldName
  search?: string
  limit?: number
}
