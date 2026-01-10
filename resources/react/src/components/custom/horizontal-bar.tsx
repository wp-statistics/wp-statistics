import { ChevronDown, ChevronUp } from 'lucide-react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { semanticColors } from '@/constants/design-tokens'
import { cn, formatCompactNumber } from '@/lib/utils'

export interface HorizontalBarProps {
  icon?: React.ReactNode
  label: string
  value: string | number
  percentage: string | number
  fillPercentage?: number // 0-100, proportion of total for bar fill
  isNegative?: boolean
  tooltipTitle?: string
  tooltipSubtitle?: string
  isFirst?: boolean
}

export function HorizontalBar({
  icon,
  label,
  value,
  percentage,
  fillPercentage,
  isNegative = false,
  tooltipTitle,
  tooltipSubtitle,
  isFirst = false,
}: HorizontalBarProps) {
  // Ensure label is always a string
  const safeLabel = label || ''

  // Use fillPercentage for bar width (proportion of total), default to 0 if not provided
  const barWidth = fillPercentage !== undefined ? Math.min(Math.max(fillPercentage, 0), 100) : 0

  // Don't show decimals when percentage >= 100
  const percentageNum = typeof percentage === 'string' ? parseFloat(percentage) : percentage
  const displayPercentage = percentageNum >= 100 ? Math.round(percentageNum) : percentage

  // Format value with compact notation
  const valueNum = typeof value === 'string' ? parseFloat(value) : value
  const displayValue = Number.isFinite(valueNum) ? formatCompactNumber(valueNum) : value

  // Truncate long labels
  const maxLabelLength = 25
  const needsTruncation = safeLabel.length > maxLabelLength
  const truncatedLabel = needsTruncation ? `${safeLabel.substring(0, maxLabelLength - 1)}…` : safeLabel

  const content = (
    <div
      className={cn(
        'relative bg-background rounded-sm overflow-hidden w-full',
        // Responsive padding
        'p-3 md:p-2'
      )}
    >
      <div className="absolute inset-0 transition-all bg-primary/10" style={{ width: `${barWidth}%` }} />

      <div className="relative flex items-center justify-between gap-3 md:gap-4">
        <div className="flex items-center gap-2 leading-0 min-w-0">
          {icon && <span className="text-lg md:text-xl leading-none shrink-0">{icon}</span>}
          {needsTruncation ? (
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="text-xs font-medium text-neutral-700 truncate max-w-[120px] md:max-w-[200px]">
                  {truncatedLabel}
                </span>
              </TooltipTrigger>
              <TooltipContent side="top">{safeLabel}</TooltipContent>
            </Tooltip>
          ) : (
            <span className="text-xs font-medium text-neutral-700 truncate">{safeLabel}</span>
          )}
        </div>

        <div className="flex items-center gap-2 md:gap-3 shrink-0">
          <span
            className={cn(
              'text-xs tabular-nums',
              isFirst ? 'font-semibold text-neutral-900' : 'font-medium text-neutral-600'
            )}
          >
            {displayValue}
          </span>
          <span
            className={cn(
              'inline-flex items-center gap-0.5 text-[11px] md:text-xs font-medium tabular-nums',
              isNegative ? semanticColors.trendNegative : semanticColors.trendPositive
            )}
          >
            {isNegative ? (
              <ChevronDown className="h-3 w-3 -mr-0.5" strokeWidth={2.5} />
            ) : (
              <ChevronUp className="h-3 w-3 -mr-0.5" strokeWidth={2.5} />
            )}
            {displayPercentage}%
          </span>
        </div>
      </div>
    </div>
  )

  if (tooltipTitle || tooltipSubtitle) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>{content}</TooltipTrigger>
        <TooltipContent side="top" className="p-2.5">
          <div className="flex flex-col gap-1.5">
            <div className="font-medium text-neutral-100">{tooltipTitle}</div>
            <div className="flex items-center gap-1.5">
              <span className="text-sm leading-none">{icon}</span>
              <span className="text-neutral-300">{safeLabel}</span>
            </div>
            <div className="flex items-center gap-4 justify-between border-t border-neutral-700 pt-1.5 mt-0.5">
              <span className="text-neutral-400">{tooltipSubtitle}</span>
              <div className="flex items-center font-medium">
                <span className={isNegative ? semanticColors.trendNegativeLight : semanticColors.trendPositiveLight}>
                  {isNegative ? (
                    <ChevronDown className="h-3.5 w-3.5" strokeWidth={2.5} />
                  ) : (
                    <ChevronUp className="h-3.5 w-3.5" strokeWidth={2.5} />
                  )}
                </span>
                <span
                  className={cn(
                    'tabular-nums',
                    isNegative ? semanticColors.trendNegativeLight : semanticColors.trendPositiveLight
                  )}
                >
                  {displayPercentage}%
                </span>
              </div>
            </div>
          </div>
        </TooltipContent>
      </Tooltip>
    )
  }

  return content
}
