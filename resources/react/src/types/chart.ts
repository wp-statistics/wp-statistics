/**
 * Chart data types for transforming API responses to LineChart format.
 *
 * These types provide a consistent interface between the backend ChartFormatter
 * and the frontend LineChart component, enabling reusable transformation logic.
 */

/**
 * Raw API response format from ChartFormatter.php
 */
export interface ChartApiResponse {
  success: boolean
  labels: string[]
  previousLabels?: string[]
  datasets: ChartDataset[]
  meta?: {
    compare_from?: string
    compare_to?: string
    [key: string]: unknown
  }
}

/**
 * Individual dataset within a chart response
 */
export interface ChartDataset {
  label: string
  key: string
  data: (number | null)[]
  comparison?: boolean
}

/**
 * Options for chart data transformation
 */
export interface TransformChartOptions {
  /**
   * Whether to preserve null values (for line gaps) or convert to 0.
   * - true: Null values remain null, causing gaps in the line chart
   * - false: Null values are converted to 0
   * @default false
   */
  preserveNull?: boolean

  /**
   * Custom key mapping for metric names.
   * Useful when combining multiple API responses with different key names.
   * @example { visitors: 'userVisitors' }
   */
  keyMapping?: Record<string, string>
}

/**
 * Configuration for a chart metric (without calculated values)
 */
export interface LineChartMetricConfig {
  /** Data key matching the dataset key */
  key: string
  /** Display label for the metric */
  label: string
  /** CSS color or CSS variable (e.g., 'var(--chart-1)') */
  color?: string
  /** Whether the metric line is initially visible */
  enabled?: boolean
}

/**
 * Full metric definition with calculated values for LineChart
 */
export interface LineChartMetric extends LineChartMetricConfig {
  /** Formatted current period total */
  value?: string
  /** Formatted previous period total */
  previousValue?: string
}

/**
 * Data point structure expected by LineChart component
 */
export interface LineChartDataPoint {
  /** Current period date (YYYY-MM-DD) */
  date: string
  /** Previous period date for tooltip (null if PP is shorter than current period) */
  previousDate?: string | null
  /** Dynamic metric values: visitors, visitorsPrevious, views, viewsPrevious, etc. */
  [key: string]: string | number | null | undefined
}

/**
 * Calculated totals for metrics
 */
export interface ChartTotals {
  [metricKey: string]: {
    current: number
    previous: number
  }
}

/**
 * Options for useChartData hook
 */
export interface UseChartDataOptions extends TransformChartOptions {
  /** Metric configurations (keys, labels, colors) */
  metrics: LineChartMetricConfig[]
  /** Whether to include previous period values in metrics */
  showPreviousValues?: boolean
}

/**
 * Result returned by useChartData hook
 */
export interface UseChartDataResult {
  /** Transformed data points ready for LineChart */
  data: LineChartDataPoint[]
  /** Metrics with calculated totals */
  metrics: LineChartMetric[]
  /** Raw totals for custom formatting */
  totals: ChartTotals
  /** Whether the response contains previous period data */
  hasPreviousPeriod: boolean
}
