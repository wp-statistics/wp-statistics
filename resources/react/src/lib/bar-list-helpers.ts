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
}

/**
 * Input data structure for creating a bar list item
 */
export interface BarListItemInput {
  /** Display name/label for the item */
  name: string
  /** Current period value */
  currentValue: number
  /** Previous period value for comparison */
  previousValue: number
  /** Total visitors/views for calculating fill percentage */
  totalValue: number
  /** Optional icon element */
  icon?: ReactNode
}

/**
 * createBarListItem - Factory function for HorizontalBarList items
 *
 * Transforms raw data into the format expected by HorizontalBarList component.
 * Handles percentage calculation, fill percentage, and tooltip generation.
 *
 * @param input - Data for creating the bar list item
 * @returns Formatted HorizontalBarItem
 *
 * @example
 * const items = countriesData.map(country => createBarListItem({
 *   name: country.country_name,
 *   currentValue: country.visitors,
 *   previousValue: country.previous?.visitors || 0,
 *   totalValue: totalVisitors,
 *   icon: <FlagIcon code={country.code} />
 * }))
 */
export function createBarListItem(input: BarListItemInput): HorizontalBarItem {
  const { name, currentValue, previousValue, totalValue, icon } = input
  const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

  return {
    label: name,
    value: currentValue,
    percentage,
    fillPercentage: calcSharePercentage(currentValue, totalValue),
    isNegative,
    icon,
    tooltipTitle: name,
    tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
  }
}

/**
 * createBarListItems - Batch create bar list items from array data
 *
 * Convenience function for mapping an array of data to HorizontalBarItems.
 *
 * @param items - Array of items with required fields
 * @param totalValue - Total value for calculating fill percentages
 * @param getIcon - Optional function to generate icon for each item
 * @returns Array of HorizontalBarItems
 *
 * @example
 * const barItems = createBarListItems(
 *   devicesData,
 *   totalVisitors,
 *   (item) => <DeviceIcon type={item.device_type_name} />
 * )
 */
export function createBarListItems<T extends {
  name: string
  visitors: number
  previous?: { visitors?: number }
}>(
  items: T[],
  totalValue: number,
  getIcon?: (item: T) => ReactNode
): HorizontalBarItem[] {
  return items.map(item => createBarListItem({
    name: item.name,
    currentValue: Number(item.visitors) || 0,
    previousValue: Number(item.previous?.visitors) || 0,
    totalValue,
    icon: getIcon?.(item),
  }))
}
