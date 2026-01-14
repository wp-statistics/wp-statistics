/**
 * Chart data transformation utilities.
 *
 * Pure functions for transforming ChartFormatter API responses
 * into LineChart-ready data structures.
 */

import type {
  ChartApiResponse,
  ChartTotals,
  LineChartDataPoint,
  LineChartMetric,
  LineChartMetricConfig,
  TransformChartOptions,
} from '@/types/chart'

import { formatDecimal } from './utils'

/**
 * Transform ChartFormatter API response to LineChart data format.
 *
 * Handles:
 * - Separating current/previous period datasets
 * - Key transformation (visitors_previous -> visitorsPrevious)
 * - Index-based alignment of previous period dates
 * - Consistent null handling based on options
 *
 * @param response - Raw API response from ChartFormatter
 * @param options - Transformation options
 * @returns Array of data points ready for LineChart
 *
 * @example
 * ```ts
 * const chartData = transformChartResponse(response, { preserveNull: true })
 * ```
 */
export function transformChartResponse(
  response: ChartApiResponse | undefined | null,
  options: TransformChartOptions = {}
): LineChartDataPoint[] {
  const { preserveNull = false, keyMapping = {} } = options

  if (!response?.labels || !response?.datasets) {
    return []
  }

  const { labels, previousLabels = [], datasets } = response

  // Separate current and previous datasets
  const currentDatasets = datasets.filter((d) => !d.comparison)
  const previousDatasets = datasets.filter((d) => d.comparison)

  return labels.map((label, index) => {
    const point: LineChartDataPoint = {
      date: label,
      // Include previous period date for tooltip (null if PP is shorter)
      previousDate: previousLabels[index] || null,
    }

    // Add current period data (always present, defaults to 0)
    currentDatasets.forEach((dataset) => {
      const targetKey = keyMapping[dataset.key] || dataset.key
      point[targetKey] = Number(dataset.data[index]) || 0
    })

    // Add previous period data with key transformation
    previousDatasets.forEach((dataset) => {
      // Convert "visitors_previous" to base key "visitors"
      const baseKey = dataset.key.replace('_previous', '')
      const targetBaseKey = keyMapping[baseKey] || baseKey
      const previousKey = `${targetBaseKey}Previous`

      const rawValue = dataset.data[index]

      // Handle null based on preserveNull option
      if (rawValue === null) {
        point[previousKey] = preserveNull ? null : 0
      } else {
        point[previousKey] = Number(rawValue) || 0
      }
    })

    return point
  })
}

/**
 * Calculate totals from chart datasets.
 *
 * @param response - Raw API response from ChartFormatter
 * @param metricKeys - Array of metric keys to calculate totals for
 * @returns Object with current and previous totals per metric
 *
 * @example
 * ```ts
 * const totals = calculateChartTotals(response, ['visitors', 'views'])
 * // { visitors: { current: 1500, previous: 1200 }, views: { current: 3000, previous: 2800 } }
 * ```
 */
export function calculateChartTotals(
  response: ChartApiResponse | undefined | null,
  metricKeys: string[]
): ChartTotals {
  const result: ChartTotals = {}

  // Initialize with zeros
  metricKeys.forEach((key) => {
    result[key] = { current: 0, previous: 0 }
  })

  if (!response?.datasets) {
    return result
  }

  const { datasets } = response

  metricKeys.forEach((key) => {
    // Find current period dataset
    const currentDataset = datasets.find((d) => d.key === key && !d.comparison)
    if (currentDataset?.data) {
      result[key].current = currentDataset.data.reduce((sum, v) => sum + (v !== null ? Number(v) : 0), 0)
    }

    // Find previous period dataset
    const previousDataset = datasets.find((d) => d.key === `${key}_previous` && d.comparison)
    if (previousDataset?.data) {
      result[key].previous = previousDataset.data.reduce((sum, v) => sum + (v !== null ? Number(v) : 0), 0)
    }
  })

  return result
}

/**
 * Format a number for display in chart metrics.
 * Uses compact notation (k) for large numbers.
 *
 * @param value - Number to format
 * @returns Formatted string
 */
export function formatChartValue(value: number): string {
  if (value >= 1000) {
    return `${formatDecimal(value / 1000)}k`
  }
  return formatDecimal(value, 0)
}

/**
 * Build LineChart metrics array with calculated values.
 *
 * @param totals - Calculated totals from calculateChartTotals
 * @param configs - Metric configurations (keys, labels, colors)
 * @param showPreviousValues - Whether to include previous period values
 * @returns Array of metrics with calculated values
 *
 * @example
 * ```ts
 * const metrics = buildChartMetrics(
 *   totals,
 *   [
 *     { key: 'visitors', label: 'Visitors', color: 'var(--chart-1)' },
 *     { key: 'views', label: 'Views', color: 'var(--chart-2)' },
 *   ],
 *   isCompareEnabled
 * )
 * ```
 */
export function buildChartMetrics(
  totals: ChartTotals,
  configs: LineChartMetricConfig[],
  showPreviousValues: boolean
): LineChartMetric[] {
  return configs.map((config) => {
    const total = totals[config.key] || { current: 0, previous: 0 }

    const metric: LineChartMetric = {
      ...config,
      enabled: config.enabled ?? true,
      value: formatChartValue(total.current),
    }

    if (showPreviousValues) {
      metric.previousValue = formatChartValue(total.previous)
    }

    return metric
  })
}

/**
 * Check if response has any previous period data.
 *
 * @param response - Raw API response from ChartFormatter
 * @returns True if response contains comparison datasets
 */
export function hasPreviousPeriodData(response: ChartApiResponse | undefined | null): boolean {
  if (!response?.datasets) {
    return false
  }
  return response.datasets.some((d) => d.comparison)
}

/**
 * Merge multiple chart API responses into a single response.
 * Useful for pages that combine data from multiple queries (e.g., logged-in-users).
 *
 * @param responses - Array of chart responses to merge
 * @param keyMappings - Key mappings per response to avoid collisions
 * @returns Merged chart response
 *
 * @example
 * ```ts
 * const merged = mergeChartResponses(
 *   [loggedInResponse, anonymousResponse],
 *   [{ visitors: 'userVisitors' }, { visitors: 'anonymousVisitors' }]
 * )
 * ```
 */
export function mergeChartResponses(
  responses: (ChartApiResponse | undefined | null)[],
  keyMappings: Record<string, string>[] = []
): ChartApiResponse | null {
  // Find first valid response for labels
  const primaryResponse = responses.find((r) => r?.labels?.length)
  if (!primaryResponse) {
    return null
  }

  const mergedDatasets: ChartApiResponse['datasets'] = []

  responses.forEach((response, responseIndex) => {
    if (!response?.datasets) return

    const keyMapping = keyMappings[responseIndex] || {}

    response.datasets.forEach((dataset) => {
      const baseKey = dataset.key.replace('_previous', '')
      const isComparison = dataset.comparison
      const mappedKey = keyMapping[baseKey] || baseKey
      const finalKey = isComparison ? `${mappedKey}_previous` : mappedKey

      mergedDatasets.push({
        ...dataset,
        key: finalKey,
        label: dataset.label, // Keep original label
      })
    })
  })

  return {
    success: true,
    labels: primaryResponse.labels,
    previousLabels: primaryResponse.previousLabels,
    datasets: mergedDatasets,
    meta: primaryResponse.meta,
  }
}
