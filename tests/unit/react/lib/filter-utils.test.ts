import { describe, it, expect } from 'vitest'
import {
  serializeFilterToBracket,
  parseBracketFilter,
  serializeFiltersToBracketParams,
  parseBracketFiltersFromParams,
  isLegacyJsonFilterFormat,
  parseLegacyJsonFilters,
  filtersToUrlFilters,
  urlFiltersToFilters,
  extractFilterField,
  type UrlFilter,
} from '@lib/filter-utils'
import type { Filter } from '@/components/custom/filter-bar'
import type { FilterField } from '@/components/custom/filter-row'

describe('filter-utils', () => {
  describe('Bracket Notation Serialization', () => {
    describe('serializeFilterToBracket', () => {
      it('should serialize single value filter', () => {
        const filter: UrlFilter = { field: 'country', operator: 'eq', value: 'US' }
        const result = serializeFilterToBracket(filter)

        expect(result.key).toBe('filter[country]')
        expect(result.value).toBe('eq:US')
      })

      it('should serialize multi-value filter', () => {
        const filter: UrlFilter = { field: 'country', operator: 'in', value: ['JP', 'CN', 'US'] }
        const result = serializeFilterToBracket(filter)

        expect(result.key).toBe('filter[country]')
        expect(result.value).toBe('in:JP,CN,US')
      })

      it('should handle contains operator', () => {
        const filter: UrlFilter = { field: 'browser', operator: 'contains', value: 'Chrome' }
        const result = serializeFilterToBracket(filter)

        expect(result.key).toBe('filter[browser]')
        expect(result.value).toBe('contains:Chrome')
      })

      it('should handle not equal operator', () => {
        const filter: UrlFilter = { field: 'os', operator: 'neq', value: 'Windows' }
        const result = serializeFilterToBracket(filter)

        expect(result.key).toBe('filter[os]')
        expect(result.value).toBe('neq:Windows')
      })

      it('should handle values with special characters', () => {
        const filter: UrlFilter = { field: 'referrer', operator: 'eq', value: 'https://example.com/path?query=1' }
        const result = serializeFilterToBracket(filter)

        expect(result.key).toBe('filter[referrer]')
        expect(result.value).toBe('eq:https://example.com/path?query=1')
      })
    })

    describe('parseBracketFilter', () => {
      it('should parse single value filter', () => {
        const result = parseBracketFilter('filter[country]', 'eq:US')

        expect(result).toEqual({
          field: 'country',
          operator: 'eq',
          value: 'US',
        })
      })

      it('should parse multi-value filter with in operator', () => {
        const result = parseBracketFilter('filter[country]', 'in:JP,CN,US')

        expect(result).toEqual({
          field: 'country',
          operator: 'in',
          value: ['JP', 'CN', 'US'],
        })
      })

      it('should parse multi-value filter with not_in operator', () => {
        const result = parseBracketFilter('filter[browser]', 'not_in:Safari,Edge')

        expect(result).toEqual({
          field: 'browser',
          operator: 'not_in',
          value: ['Safari', 'Edge'],
        })
      })

      it('should NOT split values for non-multi operators', () => {
        const result = parseBracketFilter('filter[referrer]', 'contains:example,test')

        expect(result).toEqual({
          field: 'referrer',
          operator: 'contains',
          value: 'example,test',
        })
      })

      it('should handle values with colons', () => {
        const result = parseBracketFilter('filter[referrer]', 'eq:https://example.com')

        expect(result).toEqual({
          field: 'referrer',
          operator: 'eq',
          value: 'https://example.com',
        })
      })

      it('should return null for invalid key format', () => {
        expect(parseBracketFilter('country', 'eq:US')).toBeNull()
        expect(parseBracketFilter('filters[country]', 'eq:US')).toBeNull()
        expect(parseBracketFilter('filter[]', 'eq:US')).toBeNull()
      })

      it('should return null for invalid value format', () => {
        expect(parseBracketFilter('filter[country]', 'US')).toBeNull()
        expect(parseBracketFilter('filter[country]', ':US')).toBeNull()
        expect(parseBracketFilter('filter[country]', 'eq:')).toBeNull()
      })
    })

    describe('serializeFiltersToBracketParams', () => {
      it('should serialize multiple filters', () => {
        const filters: UrlFilter[] = [
          { field: 'country', operator: 'in', value: ['JP', 'CN'] },
          { field: 'browser', operator: 'eq', value: 'Chrome' },
          { field: 'os', operator: 'neq', value: 'Windows' },
        ]

        const result = serializeFiltersToBracketParams(filters)

        expect(result).toEqual({
          'filter[country]': 'in:JP,CN',
          'filter[browser]': 'eq:Chrome',
          'filter[os]': 'neq:Windows',
        })
      })

      it('should return empty object for empty filters', () => {
        expect(serializeFiltersToBracketParams([])).toEqual({})
      })
    })

    describe('parseBracketFiltersFromParams', () => {
      it('should parse filters from URLSearchParams', () => {
        const params = new URLSearchParams()
        params.set('filter[country]', 'in:JP,CN')
        params.set('filter[browser]', 'eq:Chrome')
        params.set('date_from', '2024-01-01')

        const result = parseBracketFiltersFromParams(params)

        expect(result).toHaveLength(2)
        expect(result).toContainEqual({
          field: 'country',
          operator: 'in',
          value: ['JP', 'CN'],
        })
        expect(result).toContainEqual({
          field: 'browser',
          operator: 'eq',
          value: 'Chrome',
        })
      })

      it('should parse filters from Record object', () => {
        const params: Record<string, string> = {
          'filter[country]': 'eq:US',
          'filter[os]': 'neq:Windows',
          page: '2',
        }

        const result = parseBracketFiltersFromParams(params)

        expect(result).toHaveLength(2)
        expect(result).toContainEqual({
          field: 'country',
          operator: 'eq',
          value: 'US',
        })
        expect(result).toContainEqual({
          field: 'os',
          operator: 'neq',
          value: 'Windows',
        })
      })

      it('should ignore invalid filter params', () => {
        const params = new URLSearchParams()
        params.set('filter[country]', 'invalid')
        params.set('filter[browser]', 'eq:Chrome')

        const result = parseBracketFiltersFromParams(params)

        expect(result).toHaveLength(1)
        expect(result[0].field).toBe('browser')
      })

      it('should return empty array when no filters', () => {
        const params = new URLSearchParams()
        params.set('page', '1')

        expect(parseBracketFiltersFromParams(params)).toEqual([])
      })
    })
  })

  describe('Legacy JSON Format', () => {
    describe('isLegacyJsonFilterFormat', () => {
      it('should detect legacy JSON format', () => {
        const legacy = '[{"field":"country","operator":"eq","value":"US"}]'
        expect(isLegacyJsonFilterFormat(legacy)).toBe(true)
      })

      it('should not detect non-JSON strings', () => {
        expect(isLegacyJsonFilterFormat('filter[country]=eq:US')).toBe(false)
        expect(isLegacyJsonFilterFormat('random string')).toBe(false)
        expect(isLegacyJsonFilterFormat('')).toBe(false)
      })

      it('should not detect non-string values', () => {
        expect(isLegacyJsonFilterFormat(123)).toBe(false)
        expect(isLegacyJsonFilterFormat(null)).toBe(false)
        expect(isLegacyJsonFilterFormat(undefined)).toBe(false)
        expect(isLegacyJsonFilterFormat([])).toBe(false)
      })
    })

    describe('parseLegacyJsonFilters', () => {
      it('should parse valid legacy JSON', () => {
        const json = '[{"field":"country","operator":"eq","value":"US"}]'
        const result = parseLegacyJsonFilters(json)

        expect(result).toHaveLength(1)
        expect(result[0]).toEqual({
          field: 'country',
          operator: 'eq',
          value: 'US',
        })
      })

      it('should handle WordPress query param interference', () => {
        const malformedJson = '[{"field":"country","operator":"eq","value":"US"}]?page=wp-statistics'
        const result = parseLegacyJsonFilters(malformedJson)

        expect(result).toHaveLength(1)
        expect(result[0].field).toBe('country')
      })

      it('should parse filters with array values', () => {
        const json = '[{"field":"browser","operator":"in","value":["Chrome","Firefox"]}]'
        const result = parseLegacyJsonFilters(json)

        expect(result).toHaveLength(1)
        expect(result[0].value).toEqual(['Chrome', 'Firefox'])
      })

      it('should filter out invalid filter objects', () => {
        const json = '[{"field":"country","operator":"eq","value":"US"},{"invalid":"data"},null]'
        const result = parseLegacyJsonFilters(json)

        expect(result).toHaveLength(1)
        expect(result[0].field).toBe('country')
      })

      it('should return empty array for invalid JSON', () => {
        expect(parseLegacyJsonFilters('not valid json')).toEqual([])
        expect(parseLegacyJsonFilters('{}')).toEqual([])
        expect(parseLegacyJsonFilters('')).toEqual([])
      })
    })
  })

  describe('Filter Conversion', () => {
    const mockFilterFields: FilterField[] = [
      {
        name: 'country',
        label: 'Country',
        type: 'select',
        options: [
          { value: 'US', label: 'United States' },
          { value: 'JP', label: 'Japan' },
          { value: 'CN', label: 'China' },
        ],
      },
      {
        name: 'browser',
        label: 'Browser',
        type: 'text',
      },
    ]

    describe('urlFiltersToFilters', () => {
      it('should convert URL filters to Filter type with label resolution', () => {
        const urlFilters: UrlFilter[] = [{ field: 'country', operator: 'eq', value: 'US' }]
        const result = urlFiltersToFilters(urlFilters, mockFilterFields)

        expect(result).toHaveLength(1)
        expect(result[0].label).toBe('Country')
        expect(result[0].value).toBe('United States')
        expect(result[0].rawValue).toBe('US')
        expect(result[0].operator).toBe('eq')
      })

      it('should handle multi-value filters', () => {
        const urlFilters: UrlFilter[] = [{ field: 'country', operator: 'in', value: ['JP', 'CN'] }]
        const result = urlFiltersToFilters(urlFilters, mockFilterFields)

        expect(result).toHaveLength(1)
        expect(result[0].value).toBe('Japan, China')
        expect(result[0].rawValue).toEqual(['JP', 'CN'])
      })

      it('should use raw value when no field options', () => {
        const urlFilters: UrlFilter[] = [{ field: 'browser', operator: 'contains', value: 'Chrome' }]
        const result = urlFiltersToFilters(urlFilters, mockFilterFields)

        expect(result).toHaveLength(1)
        expect(result[0].value).toBe('Chrome')
        expect(result[0].rawValue).toBe('Chrome')
      })

      it('should use field name as label when field not found', () => {
        const urlFilters: UrlFilter[] = [{ field: 'unknown_field', operator: 'eq', value: 'test' }]
        const result = urlFiltersToFilters(urlFilters, mockFilterFields)

        expect(result).toHaveLength(1)
        expect(result[0].label).toBe('unknown_field')
      })

      it('should return empty array for undefined or empty filters', () => {
        expect(urlFiltersToFilters(undefined, mockFilterFields)).toEqual([])
        expect(urlFiltersToFilters([], mockFilterFields)).toEqual([])
      })
    })

    describe('filtersToUrlFilters', () => {
      it('should convert Filter type to URL filter format', () => {
        const filters: Filter[] = [
          {
            id: 'country-country-filter-1',
            label: 'Country',
            operator: 'eq',
            rawOperator: 'eq',
            value: 'United States',
            rawValue: 'US',
          },
        ]

        const result = filtersToUrlFilters(filters)

        expect(result).toHaveLength(1)
        expect(result[0]).toEqual({
          field: 'country',
          operator: 'eq',
          value: 'US',
        })
      })

      it('should NOT include valueLabels in output', () => {
        const filters: Filter[] = [
          {
            id: 'country-filter-1',
            label: 'Country',
            operator: 'in',
            rawOperator: 'in',
            value: 'Japan, China',
            rawValue: ['JP', 'CN'],
            valueLabels: { JP: 'Japan', CN: 'China' },
          },
        ]

        const result = filtersToUrlFilters(filters)

        expect(result[0]).not.toHaveProperty('valueLabels')
      })

      it('should use operator when rawOperator not present', () => {
        const filters: Filter[] = [
          {
            id: 'browser-filter-1',
            label: 'Browser',
            operator: 'contains',
            value: 'Chrome',
          },
        ]

        const result = filtersToUrlFilters(filters)

        expect(result[0].operator).toBe('contains')
        expect(result[0].value).toBe('Chrome')
      })
    })

    describe('extractFilterField', () => {
      it('should extract field name from filter ID', () => {
        expect(extractFilterField('country-country-filter-1234')).toBe('country')
        expect(extractFilterField('browser-filter-restored-0')).toBe('browser')
        expect(extractFilterField('simple-id')).toBe('simple')
      })
    })
  })

  describe('URL Format Examples', () => {
    it('Example: Single country filter', () => {
      // URL: filter[country]=eq:US
      const params = { 'filter[country]': 'eq:US' }
      const filters = parseBracketFiltersFromParams(params)

      expect(filters).toEqual([{ field: 'country', operator: 'eq', value: 'US' }])

      // Round-trip
      const serialized = serializeFiltersToBracketParams(filters)
      expect(serialized).toEqual(params)
    })

    it('Example: Multiple countries with in operator', () => {
      // URL: filter[country]=in:JP,CN,US
      const params = { 'filter[country]': 'in:JP,CN,US' }
      const filters = parseBracketFiltersFromParams(params)

      expect(filters).toEqual([{ field: 'country', operator: 'in', value: ['JP', 'CN', 'US'] }])

      // Round-trip
      const serialized = serializeFiltersToBracketParams(filters)
      expect(serialized).toEqual(params)
    })

    it('Example: Multiple different filters', () => {
      // URL: filter[country]=in:JP,CN&filter[browser]=eq:Chrome&filter[os]=neq:Windows
      const params = {
        'filter[country]': 'in:JP,CN',
        'filter[browser]': 'eq:Chrome',
        'filter[os]': 'neq:Windows',
      }
      const filters = parseBracketFiltersFromParams(params)

      expect(filters).toHaveLength(3)

      // Round-trip
      const serialized = serializeFiltersToBracketParams(filters)
      expect(serialized).toEqual(params)
    })

    it('Example: URL with colons in value', () => {
      // URL: filter[referrer]=eq:https://example.com/path
      const params = { 'filter[referrer]': 'eq:https://example.com/path' }
      const filters = parseBracketFiltersFromParams(params)

      expect(filters).toEqual([
        { field: 'referrer', operator: 'eq', value: 'https://example.com/path' },
      ])
    })

    it('Example: Contains operator with comma in value (not split)', () => {
      // URL: filter[search_term]=contains:hello,world
      const params = { 'filter[search_term]': 'contains:hello,world' }
      const filters = parseBracketFiltersFromParams(params)

      expect(filters).toEqual([
        { field: 'search_term', operator: 'contains', value: 'hello,world' },
      ])
    })
  })
})
