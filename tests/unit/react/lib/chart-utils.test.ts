import { describe, expect, it } from 'vitest'

import {
  buildChartMetrics,
  calculateChartTotals,
  mergeChartResponses,
  transformChartResponse,
} from '@lib/chart-utils'
import type { ChartApiResponse, ChartTotals, LineChartMetricConfig } from '@/types/chart'

describe('chart-utils', () => {
  // Sample API response (uses camelCase previousLabels)
  const mockResponse: ChartApiResponse = {
    success: true,
    labels: ['2025-01-01', '2025-01-02', '2025-01-03'],
    datasets: [
      { label: 'Visitors', key: 'visitors', data: [100, 150, 120], comparison: false },
      { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80, 90, 100], comparison: true },
      { label: 'Views', key: 'views', data: [200, 300, 250], comparison: false },
      { label: 'Views (Previous)', key: 'views_previous', data: [150, 200, 180], comparison: true },
    ],
    previousLabels: ['2024-12-01', '2024-12-02', '2024-12-03'],
  }

  describe('transformChartResponse', () => {
    it('transforms API response to chart data format', () => {
      const result = transformChartResponse(mockResponse)

      expect(result).toHaveLength(3)
      expect(result[0]).toEqual({
        date: '2025-01-01',
        previousDate: '2024-12-01',
        visitors: 100,
        visitorsPrevious: 80,
        views: 200,
        viewsPrevious: 150,
      })
    })

    it('returns empty array for null response', () => {
      expect(transformChartResponse(null)).toEqual([])
    })

    it('returns empty array for undefined response', () => {
      expect(transformChartResponse(undefined)).toEqual([])
    })

    it('returns empty array when labels missing', () => {
      const noLabels = { success: true, datasets: mockResponse.datasets } as ChartApiResponse
      expect(transformChartResponse(noLabels)).toEqual([])
    })

    it('returns empty array when datasets missing', () => {
      const noDatasets = { success: true, labels: mockResponse.labels } as ChartApiResponse
      expect(transformChartResponse(noDatasets)).toEqual([])
    })

    it('handles _previous suffix in dataset keys', () => {
      const response: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80], comparison: true },
        ],
      }

      const result = transformChartResponse(response)

      expect(result[0].visitors).toBe(100)
      expect(result[0].visitorsPrevious).toBe(80)
    })

    it('preserves null values when preserveNull=true in options', () => {
      const responseWithNulls: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100, 150], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [null, 90], comparison: true },
        ],
      }

      const result = transformChartResponse(responseWithNulls, { preserveNull: true })

      expect(result[0].visitorsPrevious).toBeNull()
      expect(result[1].visitorsPrevious).toBe(90)
    })

    it('converts null to 0 when preserveNull=false', () => {
      const responseWithNulls: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100, 150], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [null, 90], comparison: true },
        ],
      }

      const result = transformChartResponse(responseWithNulls, { preserveNull: false })

      expect(result[0].visitorsPrevious).toBe(0)
      expect(result[1].visitorsPrevious).toBe(90)
    })

    it('sets previousDate to null when previousLabels is shorter', () => {
      const shorterPP: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02', '2025-01-03'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [100, 150, 120], comparison: false }],
        previousLabels: ['2024-12-01', '2024-12-02'], // Only 2 labels
      }

      const result = transformChartResponse(shorterPP)

      expect(result[0].previousDate).toBe('2024-12-01')
      expect(result[1].previousDate).toBe('2024-12-02')
      expect(result[2].previousDate).toBeNull()
    })

    it('handles null values in previousLabels', () => {
      const nullInPreviousLabels: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02', '2025-01-03'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [100, 150, 120], comparison: false }],
        previousLabels: ['2024-12-01', undefined as unknown as string, undefined as unknown as string],
      }

      const result = transformChartResponse(nullInPreviousLabels)

      expect(result[0].previousDate).toBe('2024-12-01')
      expect(result[1].previousDate).toBeNull()
      expect(result[2].previousDate).toBeNull()
    })

    it('handles missing previousLabels array', () => {
      const noPreviousLabels: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [100, 150], comparison: false }],
      }

      const result = transformChartResponse(noPreviousLabels)

      expect(result[0].previousDate).toBeNull()
      expect(result[1].previousDate).toBeNull()
    })

    it('converts string values to numbers', () => {
      const stringData: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: ['100' as unknown as number], comparison: false }],
      }

      const result = transformChartResponse(stringData)

      expect(result[0].visitors).toBe(100)
      expect(typeof result[0].visitors).toBe('number')
    })

    it('uses keyMapping to rename keys', () => {
      const response: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80], comparison: true },
        ],
      }

      const result = transformChartResponse(response, { keyMapping: { visitors: 'userVisitors' } })

      expect(result[0].userVisitors).toBe(100)
      expect(result[0].userVisitorsPrevious).toBe(80)
      expect(result[0].visitors).toBeUndefined()
    })
  })

  describe('calculateChartTotals', () => {
    it('calculates totals for all metrics', () => {
      const metricKeys = ['visitors', 'views']

      const result = calculateChartTotals(mockResponse, metricKeys)

      // visitors: 100+150+120=370
      expect(result.visitors.current).toBe(370)
      // visitors_previous: 80+90+100=270
      expect(result.visitors.previous).toBe(270)
      // views: 200+300+250=750
      expect(result.views.current).toBe(750)
      // views_previous: 150+200+180=530
      expect(result.views.previous).toBe(530)
    })

    it('returns zeros for null response', () => {
      const metricKeys = ['visitors']

      const result = calculateChartTotals(null, metricKeys)

      expect(result.visitors.current).toBe(0)
      expect(result.visitors.previous).toBe(0)
    })

    it('handles null values in dataset (excludes from sum)', () => {
      const responseWithNulls: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02', '2025-01-03'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100, null, 120], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80, null, null], comparison: true },
        ],
      }

      const metricKeys = ['visitors']
      const result = calculateChartTotals(responseWithNulls, metricKeys)

      expect(result.visitors.current).toBe(220) // 100+120, null excluded
      expect(result.visitors.previous).toBe(80) // Only 80, nulls excluded
    })

    it('handles missing datasets gracefully', () => {
      const responseNoDatasets = { success: true, labels: ['2025-01-01'] } as ChartApiResponse

      const metricKeys = ['visitors']
      const result = calculateChartTotals(responseNoDatasets, metricKeys)

      expect(result.visitors.current).toBe(0)
      expect(result.visitors.previous).toBe(0)
    })
  })

  describe('buildChartMetrics', () => {
    const totals: ChartTotals = {
      visitors: { current: 370, previous: 270 },
      views: { current: 750, previous: 530 },
    }

    it('builds metrics array with values from totals', () => {
      const metricConfigs: LineChartMetricConfig[] = [
        { key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' },
        { key: 'views', label: 'Views', color: 'var(--chart-2)' },
      ]

      const result = buildChartMetrics(totals, metricConfigs, false)

      expect(result).toHaveLength(2)
      expect(result[0].key).toBe('visitors')
      expect(result[0].label).toBe('Visitors')
      expect(result[0].color).toBe('var(--chart-1)')
      expect(result[0].enabled).toBe(true)
    })

    it('includes previousValue when showPreviousValues=true', () => {
      const metricConfigs: LineChartMetricConfig[] = [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }]

      const result = buildChartMetrics(totals, metricConfigs, true)

      expect(result[0].value).toBeDefined()
      expect(result[0].previousValue).toBeDefined()
    })

    it('excludes previousValue when showPreviousValues=false', () => {
      const metricConfigs: LineChartMetricConfig[] = [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }]

      const result = buildChartMetrics(totals, metricConfigs, false)

      expect(result[0].value).toBeDefined()
      expect(result[0].previousValue).toBeUndefined()
    })

    it('formats values >= 1000 with k suffix', () => {
      const bigTotals: ChartTotals = { visitors: { current: 5000, previous: 3500 } }
      const metricConfigs: LineChartMetricConfig[] = [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }]

      const result = buildChartMetrics(bigTotals, metricConfigs, false)

      expect(result[0].value).toContain('k')
    })

    it('does not add k suffix for values < 1000', () => {
      const smallTotals: ChartTotals = { visitors: { current: 500, previous: 300 } }
      const metricConfigs: LineChartMetricConfig[] = [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }]

      const result = buildChartMetrics(smallTotals, metricConfigs, false)

      expect(result[0].value).not.toContain('k')
    })

    it('handles missing metric key in totals', () => {
      const emptyTotals: ChartTotals = {}
      const metricConfigs: LineChartMetricConfig[] = [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }]

      const result = buildChartMetrics(emptyTotals, metricConfigs, false)

      expect(result[0].value).toBe('0') // formatDecimal(0)
    })
  })

  describe('mergeChartResponses', () => {
    const response1: ChartApiResponse = {
      success: true,
      labels: ['2025-01-01', '2025-01-02'],
      datasets: [
        { label: 'Visitors', key: 'visitors', data: [100, 150], comparison: false },
        { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80, 90], comparison: true },
      ],
      previousLabels: ['2024-12-01', '2024-12-02'],
    }

    const response2: ChartApiResponse = {
      success: true,
      labels: ['2025-01-01', '2025-01-02'],
      datasets: [
        { label: 'Visitors', key: 'visitors', data: [50, 75], comparison: false },
        { label: 'Visitors (Previous)', key: 'visitors_previous', data: [40, 45], comparison: true },
      ],
      previousLabels: ['2024-12-01', '2024-12-02'],
    }

    it('merges multiple responses into one', () => {
      const result = mergeChartResponses([response1, response2], [{ visitors: 'userVisitors' }, { visitors: 'anonVisitors' }])

      expect(result).not.toBeNull()
      expect(result?.labels).toEqual(['2025-01-01', '2025-01-02'])
    })

    it('renames keys according to keyMappings', () => {
      const result = mergeChartResponses([response1, response2], [{ visitors: 'userVisitors' }, { visitors: 'anonVisitors' }])

      const datasetKeys = result?.datasets.map((d) => d.key)
      expect(datasetKeys).toContain('userVisitors')
      expect(datasetKeys).toContain('userVisitors_previous')
      expect(datasetKeys).toContain('anonVisitors')
      expect(datasetKeys).toContain('anonVisitors_previous')
    })

    it('returns null when all responses are null/undefined', () => {
      const result = mergeChartResponses([null, undefined], [])
      expect(result).toBeNull()
    })

    it('returns null when responses array is empty', () => {
      const result = mergeChartResponses([], [])
      expect(result).toBeNull()
    })

    it('handles single response', () => {
      const result = mergeChartResponses([response1], [{ visitors: 'singleVisitors' }])

      expect(result).not.toBeNull()
      expect(result?.datasets.some((d) => d.key === 'singleVisitors')).toBe(true)
    })

    it('uses labels from first valid response', () => {
      const result = mergeChartResponses([null, response1, response2], [{}, { visitors: 'v1' }, { visitors: 'v2' }])

      expect(result?.labels).toEqual(response1.labels)
    })

    it('preserves previousLabels from first valid response', () => {
      const result = mergeChartResponses([response1, response2], [{ visitors: 'v1' }, { visitors: 'v2' }])

      expect(result?.previousLabels).toEqual(response1.previousLabels)
    })

    it('handles empty keyMappings (keeps original keys)', () => {
      const singleResponse: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [100], comparison: false }],
      }

      const result = mergeChartResponses([singleResponse], [{}])

      expect(result?.datasets[0].key).toBe('visitors')
    })

    it('handles responses with different dataset lengths', () => {
      const shortResponse: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [100, 150], comparison: false }],
      }

      const longResponse: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [50, 75], comparison: false },
          { label: 'Views', key: 'views', data: [200, 300], comparison: false },
        ],
      }

      const result = mergeChartResponses(
        [shortResponse, longResponse],
        [{ visitors: 'shortVisitors' }, { visitors: 'longVisitors', views: 'longViews' }]
      )

      expect(result?.datasets).toHaveLength(3)
    })
  })
})

describe('chart-utils edge cases', () => {
  describe('transformChartResponse - complex scenarios', () => {
    it('handles very large numbers', () => {
      const bigNumbers: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [1000000000], comparison: false }],
      }

      const result = transformChartResponse(bigNumbers)

      expect(result[0].visitors).toBe(1000000000)
    })

    it('handles decimal numbers', () => {
      const decimals: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'Rate', key: 'rate', data: [45.67], comparison: false }],
      }

      const result = transformChartResponse(decimals)

      expect(result[0].rate).toBe(45.67)
    })

    it('handles empty datasets array', () => {
      const emptyDatasets: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [],
      }

      const result = transformChartResponse(emptyDatasets)

      expect(result).toHaveLength(1)
      expect(result[0].date).toBe('2025-01-01')
    })

    it('handles multiple comparison datasets', () => {
      const multiComparison: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80], comparison: true },
          { label: 'Views', key: 'views', data: [200], comparison: false },
          { label: 'Views (Previous)', key: 'views_previous', data: [150], comparison: true },
          { label: 'Sessions', key: 'sessions', data: [50], comparison: false },
          { label: 'Sessions (Previous)', key: 'sessions_previous', data: [40], comparison: true },
        ],
      }

      const result = transformChartResponse(multiComparison)

      expect(result[0].visitors).toBe(100)
      expect(result[0].visitorsPrevious).toBe(80)
      expect(result[0].views).toBe(200)
      expect(result[0].viewsPrevious).toBe(150)
      expect(result[0].sessions).toBe(50)
      expect(result[0].sessionsPrevious).toBe(40)
    })
  })

  describe('mergeChartResponses - complex scenarios', () => {
    it('handles three or more responses', () => {
      const r1: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'A', key: 'a', data: [10], comparison: false }],
      }
      const r2: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'B', key: 'b', data: [20], comparison: false }],
      }
      const r3: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'C', key: 'c', data: [30], comparison: false }],
      }

      const result = mergeChartResponses([r1, r2, r3], [{ a: 'first' }, { b: 'second' }, { c: 'third' }])

      expect(result?.datasets).toHaveLength(3)
      expect(result?.datasets.map((d) => d.key)).toContain('first')
      expect(result?.datasets.map((d) => d.key)).toContain('second')
      expect(result?.datasets.map((d) => d.key)).toContain('third')
    })

    it('skips null responses in the middle', () => {
      const r1: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'A', key: 'a', data: [10], comparison: false }],
      }
      const r2: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'B', key: 'b', data: [20], comparison: false }],
      }

      const result = mergeChartResponses([r1, null, r2], [{ a: 'first' }, {}, { b: 'third' }])

      expect(result?.datasets).toHaveLength(2)
    })
  })
})
