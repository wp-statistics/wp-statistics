import { useCallback } from 'react'

import { formatDecimal } from '@/lib/utils'

export interface PercentageResult {
  percentage: string
  isNegative: boolean
}

/**
 * usePercentageCalc - Hook for calculating percentage change between periods
 *
 * Calculates: ((Current - Previous) / Previous) × 100
 *
 * @returns A memoized function that calculates percentage change
 *
 * @example
 * const calcPercentage = usePercentageCalc()
 * const { percentage, isNegative } = calcPercentage(150, 100)
 * // Result: { percentage: '50', isNegative: false }
 *
 * @example
 * // In a component with metrics
 * const calcPercentage = usePercentageCalc()
 * const metrics = data.map(item => ({
 *   ...item,
 *   ...calcPercentage(item.current, item.previous)
 * }))
 */
export function usePercentageCalc() {
  return useCallback((current: number, previous: number): PercentageResult => {
    // If both are 0, no change
    if (previous === 0 && current === 0) {
      return { percentage: '0', isNegative: false }
    }
    // If previous is 0 but current > 0, show 100% increase
    if (previous === 0) {
      return { percentage: '100', isNegative: false }
    }
    const change = ((current - previous) / previous) * 100
    return {
      percentage: formatDecimal(Math.abs(change)),
      isNegative: change < 0,
    }
  }, [])
}

/**
 * calcPercentage - Non-hook version for use outside React components
 *
 * @param current - Current period value
 * @param previous - Previous period value
 * @returns Object with percentage string and isNegative flag
 */
export function calcPercentage(current: number, previous: number): PercentageResult {
  if (previous === 0 && current === 0) {
    return { percentage: '0', isNegative: false }
  }
  if (previous === 0) {
    return { percentage: '100', isNegative: false }
  }
  const change = ((current - previous) / previous) * 100
  return {
    percentage: formatDecimal(Math.abs(change)),
    isNegative: change < 0,
  }
}
