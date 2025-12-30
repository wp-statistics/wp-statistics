import type { Filter } from '@/components/custom/filter-bar'
import type { FilterField } from '@/components/custom/filter-row'

/**
 * URL filter format used in route search params
 */
export interface UrlFilter {
  field: string
  operator: string
  value: string | string[]
  /** Maps raw values to display labels (for searchable filters) */
  valueLabels?: Record<string, string>
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
 * Used when reading filters from URL search params
 */
export const urlFiltersToFilters = (
  urlFilters: UrlFilter[] | undefined,
  filterFields: FilterField[]
): Filter[] => {
  if (!urlFilters || !Array.isArray(urlFilters) || urlFilters.length === 0) return []

  return urlFilters.map((urlFilter, index) => {
    const field = filterFields.find((f) => f.name === urlFilter.field)
    const label = field?.label || urlFilter.field

    // Get display value - priority: stored valueLabels > field options > raw value
    let displayValue = Array.isArray(urlFilter.value) ? urlFilter.value.join(', ') : String(urlFilter.value)
    let resolvedValueLabels: Record<string, string> | undefined = urlFilter.valueLabels

    // Try to resolve labels from stored valueLabels first (for searchable filters)
    if (urlFilter.valueLabels && Object.keys(urlFilter.valueLabels).length > 0) {
      const values = Array.isArray(urlFilter.value) ? urlFilter.value : [urlFilter.value]
      const labels = values.map((v) => urlFilter.valueLabels?.[v] || v).join(', ')
      displayValue = labels
    }
    // Fall back to field options (for dropdown filters)
    else if (field?.options) {
      const values = Array.isArray(urlFilter.value) ? urlFilter.value : [urlFilter.value]
      resolvedValueLabels = {}
      const labels = values.map((v) => {
        const option = field.options?.find((o) => String(o.value) === v)
        if (option) {
          resolvedValueLabels![v] = option.label
          return option.label
        }
        return v
      }).join(', ')
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
 */
export const filtersToUrlFilters = (filters: Filter[]): UrlFilter[] => {
  return filters.map((filter) => {
    const urlFilter: UrlFilter = {
      field: extractFilterField(filter.id),
      operator: filter.rawOperator || filter.operator,
      value: filter.rawValue || filter.value,
    }
    // Preserve valueLabels for searchable filters (so labels can be restored)
    if (filter.valueLabels && Object.keys(filter.valueLabels).length > 0) {
      urlFilter.valueLabels = filter.valueLabels
    }
    return urlFilter
  })
}
