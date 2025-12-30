/**
 * NumericCell - Displays right-aligned numeric value with tabular formatting
 */

import { memo } from 'react'

interface NumericCellProps {
  value: number
  suffix?: string
  decimals?: number
}

export const NumericCell = memo(function NumericCell({ value, suffix, decimals }: NumericCellProps) {
  let displayValue: string

  if (decimals !== undefined) {
    // Format with fixed decimals, removing trailing .0
    const fixed = value.toFixed(decimals)
    displayValue = fixed.endsWith('.0') ? fixed.slice(0, -2) : fixed
  } else {
    displayValue = value.toLocaleString()
  }

  return (
    <div className="text-right">
      <span className="tabular-nums font-medium text-neutral-700">
        {displayValue}
        {suffix}
      </span>
    </div>
  )
})
