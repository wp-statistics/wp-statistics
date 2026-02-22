/**
 * DurationCell - Displays duration in smart time format (mm:ss or h:mm:ss)
 * Optionally shows comparison percentage badge when previousSeconds is provided
 */

import { __ } from '@wordpress/i18n'
import { ChevronDown, ChevronUp } from 'lucide-react'
import { memo } from 'react'

import { ComparisonTooltipHeader } from '@/components/custom/comparison-tooltip-header'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { semanticColors } from '@/constants/design-tokens'
import { calcPercentage } from '@/hooks/use-percentage-calc'
import { cn } from '@/lib/utils'

interface DurationCellProps {
  seconds: number
  /** Previous period duration in seconds for comparison */
  previousSeconds?: number
  /** Date range comparison label for tooltip */
  comparisonLabel?: string
}

/**
 * Format seconds to time string
 * Shows hours only when > 0: "5:30" or "1:05:30"
 */
function formatDuration(seconds: number): string {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60

  if (hours > 0) {
    return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }
  return `${minutes}:${String(secs).padStart(2, '0')}`
}

export const DurationCell = memo(function DurationCell({
  seconds,
  previousSeconds,
  comparisonLabel,
}: DurationCellProps) {
  // Calculate comparison if previousSeconds is provided
  const hasComparison = previousSeconds !== undefined
  const comparison = hasComparison ? calcPercentage(seconds, previousSeconds) : null
  const isZero = comparison?.percentage === '0'

  return (
    <div className="text-right flex items-center justify-end gap-1.5">
      <span className="text-xs tabular-nums font-medium text-neutral-700">{formatDuration(seconds)}</span>
      {hasComparison && comparison && (
        <ComparisonBadge
          percentage={comparison.percentage}
          isNegative={comparison.isNegative}
          isZero={isZero}
          comparisonLabel={comparisonLabel}
          previousValue={formatDuration(previousSeconds)}
        />
      )}
    </div>
  )
})

interface ComparisonBadgeProps {
  percentage: string
  isNegative: boolean
  isZero: boolean
  comparisonLabel?: string
  previousValue: string
}

function ComparisonBadge({ percentage, isNegative, isZero, comparisonLabel, previousValue }: ComparisonBadgeProps) {
  // Don't show decimals when percentage >= 100
  const displayPercentage = parseFloat(percentage) >= 100 ? Math.round(parseFloat(percentage)) : percentage

  const badgeContent = (
    <span
      className={cn(
        'inline-flex items-center gap-0.5 font-medium tabular-nums leading-none text-[11px]',
        isZero
          ? semanticColors.trendNeutral
          : isNegative
            ? semanticColors.trendNegative
            : semanticColors.trendPositive,
        comparisonLabel && 'cursor-help'
      )}
    >
      {!isZero &&
        (isNegative ? (
          <ChevronDown className="h-2.5 w-2.5 -mr-0.5" strokeWidth={2.5} />
        ) : (
          <ChevronUp className="h-2.5 w-2.5 -mr-0.5" strokeWidth={2.5} />
        ))}
      {displayPercentage}%
    </span>
  )

  // Wrap in tooltip if comparison label is provided
  if (comparisonLabel) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>{badgeContent}</TooltipTrigger>
        <TooltipContent side="top" className="px-2.5 py-1.5">
          <ComparisonTooltipHeader label={comparisonLabel} />
          <div className="flex items-center gap-4 justify-between">
            <span className="text-neutral-100">
              {__('Previous:', 'wp-statistics')} {previousValue}
            </span>
            <div className="flex items-center font-medium">
              <span className={isNegative ? semanticColors.trendNegativeLight : semanticColors.trendPositiveLight}>
                {!isZero &&
                  (isNegative ? (
                    <ChevronDown className="h-3.5 w-3.5" strokeWidth={2.5} />
                  ) : (
                    <ChevronUp className="h-3.5 w-3.5" strokeWidth={2.5} />
                  ))}
              </span>
              <span
                className={cn(
                  'tabular-nums',
                  isZero
                    ? 'text-neutral-300'
                    : isNegative
                      ? semanticColors.trendNegativeLight
                      : semanticColors.trendPositiveLight
                )}
              >
                {displayPercentage}%
              </span>
            </div>
          </div>
        </TooltipContent>
      </Tooltip>
    )
  }

  return badgeContent
}
