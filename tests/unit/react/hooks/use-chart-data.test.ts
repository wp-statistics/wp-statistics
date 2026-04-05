import { renderHook } from '@testing-library/react'
import { describe, expect, it } from 'vitest'

import { useChartData } from '@hooks/use-chart-data'
import type { ChartApiResponse, LineChartMetricConfig } from '@/types/chart'

describe('useChartData', () => {
  // Sample API response matching ChartFormatter output (uses camelCase previousLabels)
  const mockApiResponse: ChartApiResponse = {
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

  const defaultMetrics: LineChartMetricConfig[] = [
    { key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' },
    { key: 'views', label: 'Views', color: 'var(--chart-2)' },
  ]

  describe('Data Transformation', () => {
    it('transforms API response to chart data format', () => {
      const { result } = renderHook(() =>
        useChartData(mockApiResponse, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      expect(result.current.data).toHaveLength(3)
      expect(result.current.data[0]).toEqual({
        date: '2025-01-01',
        previousDate: '2024-12-01',
        visitors: 100,
        visitorsPrevious: 80,
        views: 200,
        viewsPrevious: 150,
      })
    })

    it('returns empty array when response is null', () => {
      const { result } = renderHook(() =>
        useChartData(null, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      expect(result.current.data).toEqual([])
    })

    it('returns empty array when response is undefined', () => {
      const { result } = renderHook(() =>
        useChartData(undefined, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      expect(result.current.data).toEqual([])
    })

    it('returns empty array when labels are missing', () => {
      const responseNoLabels = { success: true, datasets: mockApiResponse.datasets } as ChartApiResponse
      const { result } = renderHook(() =>
        useChartData(responseNoLabels, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      expect(result.current.data).toEqual([])
    })

    it('returns empty array when datasets are missing', () => {
      const responseNoDatasets = { success: true, labels: mockApiResponse.labels } as ChartApiResponse
      const { result } = renderHook(() =>
        useChartData(responseNoDatasets, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      expect(result.current.data).toEqual([])
    })
  })

  describe('Previous Period Handling', () => {
    it('includes previousDate from previousLabels', () => {
      const { result } = renderHook(() =>
        useChartData(mockApiResponse, {
          metrics: defaultMetrics,
          showPreviousValues: true,
        })
      )

      expect(result.current.data[0].previousDate).toBe('2024-12-01')
      expect(result.current.data[1].previousDate).toBe('2024-12-02')
    })

    it('sets previousDate to null when previousLabels is shorter', () => {
      const responseWithShorterPP: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02', '2025-01-03'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100, 150, 120], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80, 90, null], comparison: true },
        ],
        previousLabels: ['2024-12-01', '2024-12-02'], // Only 2 labels
      }

      const { result } = renderHook(() =>
        useChartData(responseWithShorterPP, {
          metrics: [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }],
          showPreviousValues: true,
        })
      )

      expect(result.current.data[0].previousDate).toBe('2024-12-01')
      expect(result.current.data[1].previousDate).toBe('2024-12-02')
      expect(result.current.data[2].previousDate).toBeNull()
    })

    it('handles missing previousLabels array', () => {
      const responseNoPreviousLabels: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100, 150], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80, 90], comparison: true },
        ],
      }

      const { result } = renderHook(() =>
        useChartData(responseNoPreviousLabels, {
          metrics: [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }],
          showPreviousValues: true,
        })
      )

      expect(result.current.data[0].previousDate).toBeNull()
      expect(result.current.data[1].previousDate).toBeNull()
    })
  })

  describe('Null Value Preservation', () => {
    it('preserves null values when preserveNull=true', () => {
      const responseWithNulls: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02', '2025-01-03'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100, 150, 120], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80, null, null], comparison: true },
        ],
        previousLabels: ['2024-12-01', null as unknown as string, null as unknown as string],
      }

      const { result } = renderHook(() =>
        useChartData(responseWithNulls, {
          metrics: [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }],
          showPreviousValues: true,
          preserveNull: true,
        })
      )

      expect(result.current.data[0].visitorsPrevious).toBe(80)
      expect(result.current.data[1].visitorsPrevious).toBeNull()
      expect(result.current.data[2].visitorsPrevious).toBeNull()
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

      const { result } = renderHook(() =>
        useChartData(responseWithNulls, {
          metrics: [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }],
          showPreviousValues: true,
          preserveNull: false,
        })
      )

      expect(result.current.data[0].visitorsPrevious).toBe(0)
      expect(result.current.data[1].visitorsPrevious).toBe(90)
    })
  })

  describe('Metrics Building', () => {
    it('builds metrics with totals and formatted values', () => {
      const { result } = renderHook(() =>
        useChartData(mockApiResponse, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      expect(result.current.metrics).toHaveLength(2)
      expect(result.current.metrics[0].key).toBe('visitors')
      expect(result.current.metrics[0].label).toBe('Visitors')
      expect(result.current.metrics[0].color).toBe('var(--chart-1)')
      expect(result.current.metrics[0].enabled).toBe(true)
    })

    it('includes previousValue when showPreviousValues=true', () => {
      const { result } = renderHook(() =>
        useChartData(mockApiResponse, {
          metrics: defaultMetrics,
          showPreviousValues: true,
        })
      )

      // Visitors: 100+150+120=370, Previous: 80+90+100=270
      expect(result.current.metrics[0].value).toBeDefined()
      expect(result.current.metrics[0].previousValue).toBeDefined()
    })

    it('excludes previousValue when showPreviousValues=false', () => {
      const { result } = renderHook(() =>
        useChartData(mockApiResponse, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      expect(result.current.metrics[0].previousValue).toBeUndefined()
    })

    it('formats values >= 1000 with k suffix', () => {
      const bigNumberResponse: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [5000], comparison: false }],
      }

      const { result } = renderHook(() =>
        useChartData(bigNumberResponse, {
          metrics: [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }],
          showPreviousValues: false,
        })
      )

      expect(result.current.metrics[0].value).toContain('k')
    })

    it('does not add k suffix for values < 1000', () => {
      const smallNumberResponse: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [500], comparison: false }],
      }

      const { result } = renderHook(() =>
        useChartData(smallNumberResponse, {
          metrics: [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }],
          showPreviousValues: false,
        })
      )

      expect(result.current.metrics[0].value).not.toContain('k')
    })
  })

  describe('Totals Calculation', () => {
    it('calculates totals for current period', () => {
      const { result } = renderHook(() =>
        useChartData(mockApiResponse, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      // Visitors: 100+150+120=370
      expect(result.current.totals.visitors.current).toBe(370)
      // Views: 200+300+250=750
      expect(result.current.totals.views.current).toBe(750)
    })

    it('calculates totals for previous period', () => {
      const { result } = renderHook(() =>
        useChartData(mockApiResponse, {
          metrics: defaultMetrics,
          showPreviousValues: true,
        })
      )

      // Visitors Previous: 80+90+100=270
      expect(result.current.totals.visitors.previous).toBe(270)
      // Views Previous: 150+200+180=530
      expect(result.current.totals.views.previous).toBe(530)
    })

    it('handles null values in totals calculation (excludes nulls)', () => {
      const responseWithNulls: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01', '2025-01-02', '2025-01-03'],
        datasets: [
          { label: 'Visitors', key: 'visitors', data: [100, 150, 120], comparison: false },
          { label: 'Visitors (Previous)', key: 'visitors_previous', data: [80, null, null], comparison: true },
        ],
      }

      const { result } = renderHook(() =>
        useChartData(responseWithNulls, {
          metrics: [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }],
          showPreviousValues: true,
        })
      )

      expect(result.current.totals.visitors.current).toBe(370)
      expect(result.current.totals.visitors.previous).toBe(80) // Only non-null value
    })
  })

  describe('hasPreviousPeriod Flag', () => {
    it('returns true when response has comparison datasets', () => {
      const { result } = renderHook(() =>
        useChartData(mockApiResponse, {
          metrics: defaultMetrics,
          showPreviousValues: true,
        })
      )

      expect(result.current.hasPreviousPeriod).toBe(true)
    })

    it('returns false when response has no comparison datasets', () => {
      const responseNoComparison: ChartApiResponse = {
        success: true,
        labels: ['2025-01-01'],
        datasets: [{ label: 'Visitors', key: 'visitors', data: [100], comparison: false }],
      }

      const { result } = renderHook(() =>
        useChartData(responseNoComparison, {
          metrics: [{ key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' }],
          showPreviousValues: false,
        })
      )

      expect(result.current.hasPreviousPeriod).toBe(false)
    })

    it('returns false when response is null', () => {
      const { result } = renderHook(() =>
        useChartData(null, {
          metrics: defaultMetrics,
          showPreviousValues: false,
        })
      )

      expect(result.current.hasPreviousPeriod).toBe(false)
    })
  })

  describe('Memoization', () => {
    it('returns same data structure on subsequent calls', () => {
      const options = {
        metrics: defaultMetrics,
        showPreviousValues: false,
      }

      const { result, rerender } = renderHook(
        ({ response, opts }) => useChartData(response, opts),
        { initialProps: { response: mockApiResponse, opts: options } }
      )

      const firstData = result.current.data
      const firstMetrics = result.current.metrics

      // Rerender with same props - should maintain structure
      rerender({ response: mockApiResponse, opts: options })

      // Data should have same content
      expect(result.current.data).toStrictEqual(firstData)
      expect(result.current.metrics).toStrictEqual(firstMetrics)
    })
  })
})
