import type { Filter } from '@/components/custom/filter-bar'
import type { FilterField } from '@/components/custom/filter-row'

/**
 * URL filter format used in route search params (internal representation)
 */
export interface UrlFilter {
  field: string
  operator: string
  value: string | string[]
  /** Maps raw values to display labels (for searchable filters) - NOT stored in URL */
  valueLabels?: Record<string, string>
}

/**
 * Bracket notation URL format for filters.
 *
 * Format: filter[field]=operator:value
 *
 * Examples:
 * - Single value: filter[country]=eq:US
 * - Multiple values: filter[country]=in:JP,CN
 * - Contains operator: filter[browser]=contains:Chrome
 * - Not equal: filter[os]=neq:Windows
 *
 * Special characters in values are URL-encoded automatically.
 */

/**
 * Separator used between operator and value in bracket notation
 */
const OPERATOR_VALUE_SEPARATOR = ':'

/**
 * Separator used between multiple values in bracket notation
 */
const VALUE_SEPARATOR = ','

/**
 * Prefix for filter parameters in bracket notation
 */
const FILTER_PARAM_PREFIX = 'filter['

/**
 * Serialize a single filter to bracket notation format
 * @param filter The filter to serialize
 * @returns Object with key (e.g., "filter[country]") and value (e.g., "in:JP,CN")
 */
export const serializeFilterToBracket = (filter: UrlFilter): { key: string; value: string } => {
  const values = Array.isArray(filter.value) ? filter.value : [filter.value]
  const valueString = values.join(VALUE_SEPARATOR)

  return {
    key: `filter[${filter.field}]`,
    value: `${filter.operator}${OPERATOR_VALUE_SEPARATOR}${valueString}`,
  }
}

/**
 * Parse a bracket notation filter parameter
 * @param key The parameter key (e.g., "filter[country]")
 * @param value The parameter value (e.g., "in:JP,CN")
 * @returns UrlFilter object or null if invalid
 */
export const parseBracketFilter = (key: string, value: string): UrlFilter | null => {
  // Check if key matches filter[fieldName] pattern
  const keyMatch = key.match(/^filter\[([^\]]+)\]$/)
  if (!keyMatch) return null

  const field = keyMatch[1]

  // Split value into operator and value(s) at first colon
  const separatorIndex = value.indexOf(OPERATOR_VALUE_SEPARATOR)
  if (separatorIndex === -1) return null

  const operator = value.substring(0, separatorIndex)
  const rawValue = value.substring(separatorIndex + 1)

  if (!operator || rawValue === '') return null

  // Parse value - check if it's a multi-value (contains commas)
  // For 'in' and 'not_in' operators, always split by comma
  const isMultiValueOperator = ['in', 'not_in', 'nin'].includes(operator)
  const parsedValue = isMultiValueOperator && rawValue.includes(VALUE_SEPARATOR)
    ? rawValue.split(VALUE_SEPARATOR)
    : rawValue

  return {
    field,
    operator,
    value: parsedValue,
  }
}

/**
 * Serialize multiple filters to bracket notation URL parameters
 * @param filters Array of UrlFilter objects
 * @returns Record of URL parameters (e.g., { "filter[country]": "in:JP,CN" })
 */
export const serializeFiltersToBracketParams = (filters: UrlFilter[]): Record<string, string> => {
  const params: Record<string, string> = {}

  for (const filter of filters) {
    const { key, value } = serializeFilterToBracket(filter)
    params[key] = value
  }

  return params
}

/**
 * Parse bracket notation filters from URLSearchParams or hash params
 * @param params URLSearchParams or Record of params
 * @returns Array of UrlFilter objects
 */
export const parseBracketFiltersFromParams = (
  params: URLSearchParams | Record<string, string>
): UrlFilter[] => {
  const filters: UrlFilter[] = []

  const entries = params instanceof URLSearchParams
    ? Array.from(params.entries())
    : Object.entries(params)

  for (const [key, value] of entries) {
    if (key.startsWith(FILTER_PARAM_PREFIX)) {
      const filter = parseBracketFilter(key, value)
      if (filter) {
        filters.push(filter)
      }
    }
  }

  return filters
}

/**
 * Format referrer channel for display
 * Converts API channel values to user-friendly display text
 */
export const formatReferrerChannel = (channel: string | null | undefined): string => {
  if (!channel) return 'DIRECT TRAFFIC'
  const channelMap: Record<string, string> = {
    direct: 'DIRECT TRAFFIC',
    search: 'SEARCH',
    social: 'SOCIAL',
    referral: 'REFERRAL',
    email: 'EMAIL',
    paid: 'PAID',
  }
  return channelMap[channel.toLowerCase()] || channel.toUpperCase()
}

/**
 * Extract the field name from a filter ID
 * Filter IDs are in format: "field_name-field_name-filter-..." or "field_name-index"
 */
export const extractFilterField = (filterId: string): string => {
  return filterId.split('-')[0]
}

/**
 * Convert URL filter format to Filter type for display
 * Used when reading filters from URL search params (both bracket notation and legacy JSON)
 */
export const urlFiltersToFilters = (urlFilters: UrlFilter[] | undefined, filterFields: FilterField[]): Filter[] => {
  if (!urlFilters || !Array.isArray(urlFilters) || urlFilters.length === 0) return []

  return urlFilters.map((urlFilter, index) => {
    const field = filterFields.find((f) => f.name === urlFilter.field)
    const label = field?.label || urlFilter.field

    // Get display value from field options or use raw value
    let displayValue = Array.isArray(urlFilter.value) ? urlFilter.value.join(', ') : String(urlFilter.value)
    let resolvedValueLabels: Record<string, string> | undefined

    // Resolve labels from field options (for dropdown filters)
    if (field?.options) {
      const values = Array.isArray(urlFilter.value) ? urlFilter.value : [urlFilter.value]
      resolvedValueLabels = {}
      const labels = values
        .map((v) => {
          const option = field.options?.find((o) => String(o.value) === String(v))
          if (option) {
            resolvedValueLabels![String(v)] = option.label
            return option.label
          }
          return v
        })
        .join(', ')
      displayValue = labels
      // Only keep resolvedValueLabels if we found any
      if (Object.keys(resolvedValueLabels).length === 0) {
        resolvedValueLabels = undefined
      }
    }

    // Create filter ID in the expected format: field-field-filter-restored-index
    const filterId = `${urlFilter.field}-${urlFilter.field}-filter-restored-${index}`

    return {
      id: filterId,
      label,
      operator: urlFilter.operator,
      rawOperator: urlFilter.operator,
      value: displayValue,
      rawValue: urlFilter.value,
      valueLabels: resolvedValueLabels,
    }
  })
}

/**
 * Convert URL filter format to Filter type with default filters fallback
 * Used when page has default filters that should be applied if URL has no filters
 */
export const urlFiltersToFiltersWithDefaults = (
  urlFilters: UrlFilter[] | undefined,
  filterFields: FilterField[],
  defaultFilters: Filter[]
): Filter[] => {
  if (!urlFilters || !Array.isArray(urlFilters) || urlFilters.length === 0) return defaultFilters
  return urlFiltersToFilters(urlFilters, filterFields)
}

/**
 * Convert Filter type to URL filter format for serialization
 * Used when syncing filters to URL search params
 *
 * Note: valueLabels are NOT included in URL - they are resolved from
 * filter field options when parsing the URL back to filters.
 */
export const filtersToUrlFilters = (filters: Filter[]): UrlFilter[] => {
  return filters.map((filter) => ({
    field: extractFilterField(filter.id),
    operator: filter.rawOperator || filter.operator,
    value: filter.rawValue || filter.value,
  }))
}

/**
 * Filter applied filters to only include those compatible with the current page's filter fields.
 * This prevents filters from one page (e.g., country on Visitors) from showing on pages
 * that don't support those filters (e.g., Content page).
 *
 * @param filters - The applied filters from global state
 * @param filterFields - The filter fields available on the current page
 * @returns Filters that are compatible with the current page
 */
export const getCompatibleFilters = (filters: Filter[], filterFields: FilterField[]): Filter[] => {
  if (!filters || filters.length === 0) return []
  if (!filterFields || filterFields.length === 0) return []

  const availableFieldNames = new Set(filterFields.map((f) => f.name))

  return filters.filter((filter) => {
    const fieldName = extractFilterField(filter.id)
    return availableFieldNames.has(fieldName)
  })
}
