import { describe, it, expect } from 'vitest'
import { extractFilterKey, transformFiltersToApi, type ApiFilters } from './api-filter-transform'
import type { Filter } from '@/components/custom/filter-bar'

describe('api-filter-transform', () => {
  describe('extractFilterKey', () => {
    it('should extract field name from standard filter ID', () => {
      expect(extractFilterKey('os-os-filter-1766484171552-9509610')).toBe('os')
    })

    it('should extract field name from simple filter ID', () => {
      expect(extractFilterKey('browser-123')).toBe('browser')
    })

    it('should return entire string if no hyphen present', () => {
      expect(extractFilterKey('country')).toBe('country')
    })

    it('should handle underscore field names', () => {
      expect(extractFilterKey('country_code-filter-123')).toBe('country_code')
    })
  })

  describe('transformFiltersToApi', () => {
    it('should transform a single filter with string value', () => {
      const filters: Filter[] = [
        {
          id: 'os-os-filter-123',
          label: 'Operating System',
          operator: 'eq',
          value: 'Windows',
        },
      ]

      const result = transformFiltersToApi(filters)

      expect(result).toEqual({
        os: { eq: 'Windows' },
      })
    })

    it('should transform a filter with array value', () => {
      const filters: Filter[] = [
        {
          id: 'browser-browser-filter-456',
          label: 'Browser',
          operator: 'in',
          value: ['Chrome', 'Firefox'],
        },
      ]

      const result = transformFiltersToApi(filters)

      expect(result).toEqual({
        browser: { in: ['Chrome', 'Firefox'] },
      })
    })

    it('should use rawOperator when available', () => {
      const filters: Filter[] = [
        {
          id: 'country-filter-789',
          label: 'Country',
          operator: 'equals',
          rawOperator: 'eq',
          value: 'US',
        },
      ]

      const result = transformFiltersToApi(filters)

      expect(result).toEqual({
        country: { eq: 'US' },
      })
    })

    it('should use rawValue when available', () => {
      const filters: Filter[] = [
        {
          id: 'city-filter-101',
          label: 'City',
          operator: 'eq',
          value: 'New York',
          rawValue: '123',
        },
      ]

      const result = transformFiltersToApi(filters)

      expect(result).toEqual({
        city: { eq: '123' },
      })
    })

    it('should convert numeric values to strings', () => {
      const filters: Filter[] = [
        {
          id: 'views-filter-202',
          label: 'Views',
          operator: 'gt',
          value: 100 as unknown as string, // Testing numeric value
        },
      ]

      const result = transformFiltersToApi(filters)

      expect(result).toEqual({
        views: { gt: '100' },
      })
    })

    it('should handle multiple filters', () => {
      const filters: Filter[] = [
        {
          id: 'os-filter-1',
          label: 'OS',
          operator: 'eq',
          value: 'Windows',
        },
        {
          id: 'browser-filter-2',
          label: 'Browser',
          operator: 'contains',
          value: 'Chrome',
        },
        {
          id: 'country-filter-3',
          label: 'Country',
          operator: 'in',
          value: ['US', 'UK', 'CA'],
        },
      ]

      const result = transformFiltersToApi(filters)

      expect(result).toEqual({
        os: { eq: 'Windows' },
        browser: { contains: 'Chrome' },
        country: { in: ['US', 'UK', 'CA'] },
      })
    })

    it('should return empty object for empty filters array', () => {
      const result = transformFiltersToApi([])

      expect(result).toEqual({})
    })

    it('should handle rawValue as array', () => {
      const filters: Filter[] = [
        {
          id: 'device-filter-303',
          label: 'Device',
          operator: 'in',
          value: 'Mobile, Desktop', // Display value
          rawValue: ['mobile', 'desktop'], // API values
        },
      ]

      const result = transformFiltersToApi(filters)

      expect(result).toEqual({
        device: { in: ['mobile', 'desktop'] },
      })
    })

    it('should override same filter key (last wins)', () => {
      const filters: Filter[] = [
        {
          id: 'os-filter-1',
          label: 'OS',
          operator: 'eq',
          value: 'Windows',
        },
        {
          id: 'os-filter-2',
          label: 'OS',
          operator: 'eq',
          value: 'macOS',
        },
      ]

      const result = transformFiltersToApi(filters)

      // Last filter with same key wins
      expect(result).toEqual({
        os: { eq: 'macOS' },
      })
    })
  })
})
