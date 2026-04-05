import { describe, expect, it, vi } from 'vitest'

import { transformToBarList, type HorizontalBarItem } from '@lib/bar-list-helpers'

// Mock WordPress i18n
vi.mock('@wordpress/i18n', () => ({
  __: (str: string) => str,
}))

// Mock calcPercentage - returns percentage and isNegative
vi.mock('@/hooks/use-percentage-calc', () => ({
  calcPercentage: (current: number, previous: number) => {
    if (previous === 0) return { percentage: 0, isNegative: false }
    const change = ((current - previous) / previous) * 100
    return {
      percentage: Math.abs(Math.round(change)),
      isNegative: change < 0,
    }
  },
}))

// Mock calcSharePercentage
vi.mock('@/lib/utils', () => ({
  calcSharePercentage: (value: number, total: number) => {
    if (total === 0) return 0
    return Math.round((value / total) * 100)
  },
}))

describe('bar-list-helpers', () => {
  describe('transformToBarList', () => {
    // Sample API data mimicking geographic endpoint
    const countryData = [
      { country_name: 'United States', country_code: 'US', visitors: 500, previous: { visitors: 400 } },
      { country_name: 'Germany', country_code: 'DE', visitors: 300, previous: { visitors: 350 } },
      { country_name: 'Japan', country_code: 'JP', visitors: 200, previous: { visitors: 200 } },
    ]

    describe('Basic Transformation', () => {
      it('transforms items with label and value accessors', () => {
        const result = transformToBarList(countryData, {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          total: 1000,
        })

        expect(result).toHaveLength(3)
        expect(result[0].label).toBe('United States')
        expect(result[0].value).toBe(500)
        expect(result[0].fillPercentage).toBe(50)
        expect(result[0].tooltipTitle).toBe('United States')
      })

      it('returns empty array for empty input', () => {
        const result = transformToBarList([], {
          label: (item: { name: string }) => item.name,
          value: () => 0,
          total: 100,
        })

        expect(result).toEqual([])
      })

      it('calculates fillPercentage correctly', () => {
        const result = transformToBarList(countryData, {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          total: 1000,
        })

        expect(result[0].fillPercentage).toBe(50) // 500/1000
        expect(result[1].fillPercentage).toBe(30) // 300/1000
        expect(result[2].fillPercentage).toBe(20) // 200/1000
      })

      it('handles zero total gracefully', () => {
        const result = transformToBarList(countryData.slice(0, 1), {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          total: 0,
        })

        expect(result[0].fillPercentage).toBe(0)
      })
    })

    describe('Icon Generation', () => {
      it('generates icons using the icon accessor', () => {
        const result = transformToBarList(countryData, {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          total: 1000,
          icon: (item) => `flag-${item.country_code.toLowerCase()}`,
        })

        expect(result[0].icon).toBe('flag-us')
        expect(result[1].icon).toBe('flag-de')
        expect(result[2].icon).toBe('flag-jp')
      })

      it('handles missing icon accessor', () => {
        const result = transformToBarList(countryData, {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          total: 1000,
        })

        expect(result[0].icon).toBeUndefined()
      })
    })

    describe('Comparison Mode', () => {
      it('excludes comparison props when isCompareEnabled is false', () => {
        const result = transformToBarList(countryData, {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          previousValue: (item) => item.previous.visitors,
          total: 1000,
          isCompareEnabled: false,
        })

        expect(result[0].percentage).toBeUndefined()
        expect(result[0].isNegative).toBeUndefined()
        expect(result[0].tooltipSubtitle).toBeUndefined()
        expect(result[0].comparisonDateLabel).toBeUndefined()
      })

      it('includes comparison props when isCompareEnabled is true', () => {
        const result = transformToBarList(countryData, {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          previousValue: (item) => item.previous.visitors,
          total: 1000,
          isCompareEnabled: true,
        })

        // US: 500 vs 400 = 25% increase
        expect(result[0].percentage).toBe(25)
        expect(result[0].isNegative).toBe(false)
        expect(result[0].tooltipSubtitle).toBe('Previous: 400')
      })

      it('calculates negative percentages correctly', () => {
        const result = transformToBarList(countryData, {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          previousValue: (item) => item.previous.visitors,
          total: 1000,
          isCompareEnabled: true,
        })

        // Germany: 300 vs 350 = ~14% decrease
        expect(result[1].isNegative).toBe(true)
      })

      it('handles zero previous value', () => {
        const dataWithZeroPrevious = [{ name: 'Test', value: 100, previous: { value: 0 } }]

        const result = transformToBarList(dataWithZeroPrevious, {
          label: (item) => item.name,
          value: (item) => item.value,
          previousValue: (item) => item.previous.value,
          total: 100,
          isCompareEnabled: true,
        })

        expect(result[0].percentage).toBe(0)
        expect(result[0].isNegative).toBe(false)
      })

      it('includes comparisonDateLabel when provided', () => {
        const result = transformToBarList(countryData.slice(0, 1), {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          previousValue: (item) => item.previous.visitors,
          total: 1000,
          isCompareEnabled: true,
          comparisonDateLabel: 'Dec 1-31, 2024',
        })

        expect(result[0].comparisonDateLabel).toBe('Dec 1-31, 2024')
      })
    })

    describe('Custom Tooltip Subtitle', () => {
      it('uses custom tooltipSubtitle generator', () => {
        const cityData = [{ city_name: 'New York', country_name: 'USA', visitors: 100, previous: { visitors: 80 } }]

        const result = transformToBarList(cityData, {
          label: (item) => item.city_name,
          value: (item) => item.visitors,
          previousValue: (item) => item.previous.visitors,
          total: 100,
          tooltipSubtitle: (item, prev) => `${item.country_name} • Previous: ${prev.toLocaleString()}`,
          isCompareEnabled: true,
        })

        expect(result[0].tooltipSubtitle).toBe('USA • Previous: 80')
      })

      it('falls back to default subtitle when custom not provided', () => {
        const result = transformToBarList(countryData.slice(0, 1), {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          previousValue: (item) => item.previous.visitors,
          total: 1000,
          isCompareEnabled: true,
        })

        expect(result[0].tooltipSubtitle).toBe('Previous: 400')
      })
    })

    describe('Edge Cases', () => {
      it('handles null/undefined values with fallbacks', () => {
        const dataWithNulls = [
          { name: null, visitors: undefined, previous: null },
        ] as unknown as Array<{ name: string | null; visitors: number | undefined; previous: { visitors: number } | null }>

        const result = transformToBarList(dataWithNulls, {
          label: (item) => item.name || 'Unknown',
          value: (item) => Number(item.visitors) || 0,
          previousValue: (item) => Number(item.previous?.visitors) || 0,
          total: 100,
          isCompareEnabled: true,
        })

        expect(result[0].label).toBe('Unknown')
        expect(result[0].value).toBe(0)
      })

      it('handles missing previousValue accessor', () => {
        const result = transformToBarList(countryData, {
          label: (item) => item.country_name,
          value: (item) => item.visitors,
          total: 1000,
          isCompareEnabled: true, // enabled but no previousValue accessor
        })

        // Should not include comparison props without previousValue accessor
        expect(result[0].percentage).toBeUndefined()
      })

      it('preserves type safety with generic', () => {
        interface DeviceData {
          device_name: string
          count: number
          prev_count: number
        }

        const devices: DeviceData[] = [{ device_name: 'Desktop', count: 100, prev_count: 90 }]

        const result = transformToBarList(devices, {
          label: (item) => item.device_name,
          value: (item) => item.count,
          previousValue: (item) => item.prev_count,
          total: 100,
          isCompareEnabled: true,
        })

        expect(result[0].label).toBe('Desktop')
      })
    })
  })
  describe('Integration: Real-world patterns', () => {
    it('handles geographic countries pattern', () => {
      const countries = [
        { country_name: 'USA', country_code: 'US', visitors: 1000, previous: { visitors: 800 } },
        { country_name: 'UK', country_code: 'GB', visitors: 500, previous: { visitors: 600 } },
      ]

      const result = transformToBarList(countries, {
        label: (item) => item.country_name || 'Unknown',
        value: (item) => Number(item.visitors) || 0,
        previousValue: (item) => Number(item.previous?.visitors) || 0,
        total: 1500,
        icon: (item) => `flag-${item.country_code?.toLowerCase()}`,
        isCompareEnabled: true,
        comparisonDateLabel: 'Dec 1-31, 2024',
      })

      expect(result[0]).toMatchObject({
        label: 'USA',
        value: 1000,
        icon: 'flag-us',
        isNegative: false,
      })

      expect(result[1]).toMatchObject({
        label: 'UK',
        value: 500,
        icon: 'flag-gb',
        isNegative: true,
      })
    })

    it('handles devices pattern', () => {
      const devices = [
        { device_type_name: 'Desktop', visitors: 600, previous: { visitors: 500 } },
        { device_type_name: 'Mobile', visitors: 400, previous: { visitors: 350 } },
      ]

      const result = transformToBarList(devices, {
        label: (item) => item.device_type_name,
        value: (item) => Number(item.visitors) || 0,
        previousValue: (item) => Number(item.previous?.visitors) || 0,
        total: 1000,
        isCompareEnabled: false,
      })

      expect(result).toHaveLength(2)
      expect(result[0].label).toBe('Desktop')
      expect(result[0].percentage).toBeUndefined()
    })

    it('handles cities pattern with custom tooltip', () => {
      const cities = [{ city_name: 'Berlin', country_name: 'Germany', visitors: 200, previous: { visitors: 150 } }]

      const result = transformToBarList(cities, {
        label: (item) => item.city_name || 'Unknown',
        value: (item) => Number(item.visitors) || 0,
        previousValue: (item) => Number(item.previous?.visitors) || 0,
        total: 200,
        tooltipSubtitle: (item, prev) => `${item.country_name} • Previous: ${prev.toLocaleString()}`,
        isCompareEnabled: true,
      })

      expect(result[0].tooltipSubtitle).toBe('Germany • Previous: 150')
    })
  })
})
