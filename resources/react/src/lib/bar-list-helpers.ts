import type { ReactNode } from 'react'

import { __ } from '@wordpress/i18n'

import { calcPercentage } from '@/hooks/use-percentage-calc'
import { calcSharePercentage } from '@/lib/utils'

/**
 * HorizontalBarItem interface matching the HorizontalBarList component
 */
export interface HorizontalBarItem {
  icon?: ReactNode
  label: string
  value: string | number
  percentage: string | number
  fillPercentage?: number // 0-100, proportion of total for bar fill
  isNegative?: boolean
  tooltipTitle?: string
  tooltipSubtitle?: string
  /** Date range comparison header for tooltip */
  comparisonDateLabel?: string
}

/**
 * Options for transformToBarList - flexible transformation with accessor functions
 */
export interface TransformBarListOptions<T> {
  /** Accessor for item label */
  label: (item: T) => string
  /** Accessor for current value */
  value: (item: T) => number
  /** Accessor for previous period value (for comparison) */
  previousValue?: (item: T) => number
  /** Total value for calculating fill percentages */
  total: number
  /** Optional icon generator */
  icon?: (item: T) => ReactNode
  /** Custom tooltip subtitle generator (receives item and previous value) */
  tooltipSubtitle?: (item: T, previousValue: number) => string
  /** Whether comparison mode is enabled */
  isCompareEnabled?: boolean
  /** Comparison date label for tooltip header */
  comparisonDateLabel?: string
}

/**
 * transformToBarList - Flexible transformation from API data to HorizontalBarList items
 *
 * Uses accessor functions for type-safe field mapping. Handles comparison mode toggle.
 *
 * @param items - Array of API response items
 * @param options - Transformation options with accessor functions
 * @returns Array of HorizontalBarItems
 *
 * @example
 * // Countries with flags
 * transformToBarList(countriesData, {
 *   label: (item) => item.country_name || 'Unknown',
 *   value: (item) => Number(item.visitors) || 0,
 *   previousValue: (item) => Number(item.previous?.visitors) || 0,
 *   total: totalVisitors,
 *   icon: (item) => <FlagIcon code={item.country_code} />,
 *   isCompareEnabled,
 *   comparisonDateLabel,
 * })
 *
 * @example
 * // Cities with custom tooltip
 * transformToBarList(citiesData, {
 *   label: (item) => item.city_name || 'Unknown',
 *   value: (item) => Number(item.visitors) || 0,
 *   previousValue: (item) => Number(item.previous?.visitors) || 0,
 *   total: totalVisitors,
 *   tooltipSubtitle: (item, prev) => `${item.country_name} • Previous: ${prev.toLocaleString()}`,
 *   isCompareEnabled,
 *   comparisonDateLabel,
 * })
 */
export function transformToBarList<T>(items: T[], options: TransformBarListOptions<T>): HorizontalBarItem[] {
  const {
    label: getLabel,
    value: getValue,
    previousValue: getPreviousValue,
    total,
    icon: getIcon,
    tooltipSubtitle: getTooltipSubtitle,
    isCompareEnabled = false,
    comparisonDateLabel,
  } = options

  return items.map((item) => {
    const labelText = getLabel(item)
    const currentValue = getValue(item)
    const previousValue = getPreviousValue?.(item) ?? 0

    // Build comparison props if enabled
    const comparisonProps =
      isCompareEnabled && getPreviousValue
        ? {
            ...calcPercentage(currentValue, previousValue),
            tooltipSubtitle: getTooltipSubtitle
              ? getTooltipSubtitle(item, previousValue)
              : `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
            comparisonDateLabel,
          }
        : {}

    return {
      icon: getIcon?.(item),
      label: labelText,
      value: currentValue,
      fillPercentage: calcSharePercentage(currentValue, total),
      tooltipTitle: labelText,
      ...comparisonProps,
    }
  })
}

