import { __ } from '@wordpress/i18n'
import { ChevronDown, ChevronUp, Info } from 'lucide-react'
import * as React from 'react'

import { ComparisonTooltipHeader } from '@/components/custom/comparison-tooltip-header'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { semanticColors } from '@/constants/design-tokens'
import { useBreakpoint } from '@/hooks/use-breakpoint'
import { cn } from '@/lib/utils'

export interface MetricItem {
  label: string
  value: string | number
  percentage?: string | number
  isNegative?: boolean
  icon?: React.ReactNode
  tooltipContent?: string
  /** Date range comparison header for tooltip on percentage badge */
  comparisonDateLabel?: string
  /** Previous period value for tooltip display */
  previousValue?: string | number
}

export interface MetricsProps {
  metrics: MetricItem[]
  /** Number of columns. Use 'auto' to calculate based on metrics count. */
  columns?: 1 | 2 | 3 | 4 | 6 | 12 | 'auto'
  className?: string
}

// Calculate optimal columns based on metrics count
function getOptimalColumns(count: number): 1 | 2 | 3 | 4 | 6 | 12 {
  if (count <= 1) return 1
  if (count === 2) return 2
  if (count === 3) return 3
  if (count === 5 || count === 6) return 3 // 5 metrics: 3+2, 6 metrics: 3+3
  return 4 // 4, 7, 8+ metrics: use 4 columns
}

// Balance columns to avoid single item in last row
function getBalancedColumns(count: number, requestedColumns: number): number {
  if (count <= 1) return 1

  const lastRowCount = count % requestedColumns

  // If last row has only 1 item and we have more than 1 row, try to balance
  if (lastRowCount === 1 && count > requestedColumns) {
    // Try reducing columns by 1 to get better balance
    const reducedColumns = requestedColumns - 1
    if (reducedColumns >= 2) {
      const newLastRowCount = count % reducedColumns
      // Only use reduced columns if it gives better balance (2+ items in last row)
      if (newLastRowCount === 0 || newLastRowCount >= 2) {
        return reducedColumns
      }
    }
  }

  return requestedColumns
}

export function Metrics({ metrics, columns = 'auto', className }: MetricsProps) {
  const { isMobile, isTablet } = useBreakpoint()
  const displayMetrics = metrics.slice(0, 12)

  // Calculate effective columns (resolve 'auto' to actual number)
  const effectiveColumns = React.useMemo(() => {
    if (columns === 'auto') {
      return getOptimalColumns(displayMetrics.length)
    }
    // Balance explicit columns to avoid single item in last row
    return getBalancedColumns(displayMetrics.length, columns) as 1 | 2 | 3 | 4 | 6 | 12
  }, [columns, displayMetrics.length])

  // Auto-scale columns based on breakpoint
  // Desktop: effectiveColumns value
  // Tablet: min(effectiveColumns, 3)
  // Mobile: min(effectiveColumns, 2)
  const responsiveColumns = React.useMemo(() => {
    if (isMobile) return Math.min(effectiveColumns, 2) as 1 | 2
    if (isTablet) return Math.min(effectiveColumns, 3) as 1 | 2 | 3
    return effectiveColumns
  }, [isMobile, isTablet, effectiveColumns])

  // Calculate how many items are in each row
  const totalItems = displayMetrics.length
  const totalRows = Math.ceil(totalItems / responsiveColumns)
  const lastRowItemCount = totalItems % responsiveColumns || responsiveColumns

  // Get width for an item based on its row
  const getItemWidth = (index: number) => {
    const row = Math.floor(index / responsiveColumns)
    const isLastRow = row === totalRows - 1
    // Last row items expand to fill width
    const itemsInRow = isLastRow ? lastRowItemCount : responsiveColumns
    return `${100 / itemsInRow}%`
  }

  // Calculate border classes based on position
  const getPositionClasses = (index: number) => {
    const row = Math.floor(index / responsiveColumns)
    const isFirstRow = row === 0
    const isLastRow = row === totalRows - 1

    // Check if this is the last item in its row
    const col = index % responsiveColumns
    const isLastInRow = isLastRow ? (col === lastRowItemCount - 1) : (col === responsiveColumns - 1)

    return cn(
      !isFirstRow && 'border-t border-neutral-100',
      !isLastInRow && 'border-r border-neutral-100'
    )
  }

  return (
    <div className={cn('flex flex-wrap w-full overflow-hidden', className)}>
      {displayMetrics.map((metric, index) => (
        <div key={`${metric.label}-${index}`} style={{ width: getItemWidth(index) }}>
          <MetricCard
            {...metric}
            positionClasses={getPositionClasses(index)}
            isMobile={isMobile}
          />
        </div>
      ))}
    </div>
  )
}

interface MetricCardProps extends MetricItem {
  positionClasses?: string
  isMobile?: boolean
}

function MetricCard({
  label,
  value,
  percentage,
  isNegative = false,
  icon,
  tooltipContent,
  comparisonDateLabel,
  previousValue,
  positionClasses,
  isMobile,
}: MetricCardProps) {
  const hasPercentage = percentage !== undefined && percentage !== null
  const percentageNum = typeof percentage === 'string' ? parseFloat(percentage) : percentage
  const isZero = percentageNum === 0
  // Don't show decimals when percentage >= 100
  const displayPercentage = percentageNum !== undefined && percentageNum >= 100 ? Math.round(percentageNum) : percentage

  return (
    <div
      className={cn(
        'bg-white flex flex-col justify-between',
        // Responsive padding
        'px-3 py-3 md:px-4 md:py-3.5 lg:px-5 lg:py-4',
        // Responsive min-height
        'min-h-[68px] md:min-h-[72px] lg:min-h-[76px]',
        'transition-colors duration-150',
        'hover:bg-neutral-100/60',
        positionClasses
      )}
    >
      {/* Label row with tooltip */}
      <div className="flex items-center gap-1">
        <span
          className={cn(
            'font-medium text-neutral-500 leading-none',
            // Responsive text size
            'text-xs'
          )}
        >
          {label}
        </span>
        {tooltipContent && (
          <Tooltip>
            <TooltipTrigger asChild>
              <button
                className={cn(
                  'text-neutral-300 hover:text-neutral-400 transition-colors -mt-px',
                  // Larger touch target on mobile
                  isMobile && 'p-1 -m-1'
                )}
                type="button"
                aria-label={`More information about ${label}`}
              >
                <Info className="h-3 w-3" />
              </button>
            </TooltipTrigger>
            <TooltipContent showArrow={false} side="top" className="max-w-[200px]">
              {tooltipContent}
            </TooltipContent>
          </Tooltip>
        )}
        {icon && <div className="shrink-0 ml-auto">{icon}</div>}
      </div>

      {/* Value row with percentage badge */}
      <div className="flex items-baseline gap-2 mt-2">
        <span
          className={cn(
            'font-medium text-neutral-800 leading-none tabular-nums',
            // Responsive text size
            'text-lg md:text-xl'
          )}
        >
          {value}
        </span>
        {hasPercentage && (
          <PercentageBadge
            displayPercentage={displayPercentage}
            isNegative={isNegative}
            isZero={isZero}
            comparisonDateLabel={comparisonDateLabel}
            previousValue={previousValue}
          />
        )}
      </div>
    </div>
  )
}

interface PercentageBadgeProps {
  displayPercentage: string | number | undefined
  isNegative: boolean
  isZero: boolean
  comparisonDateLabel?: string
  previousValue?: string | number
}

function PercentageBadge({
  displayPercentage,
  isNegative,
  isZero,
  comparisonDateLabel,
  previousValue,
}: PercentageBadgeProps) {
  const badgeContent = (
    <span
      className={cn(
        'inline-flex items-center gap-0.5 font-medium tabular-nums leading-none',
        // Responsive text size
        'text-[11px] md:text-xs',
        isZero
          ? semanticColors.trendNeutral
          : isNegative
            ? semanticColors.trendNegative
            : semanticColors.trendPositive,
        // Show cursor hint when tooltip is available
        comparisonDateLabel && 'cursor-help'
      )}
    >
      {!isZero &&
        (isNegative ? (
          <ChevronDown className="h-3 w-3 -mr-0.5" strokeWidth={2.5} />
        ) : (
          <ChevronUp className="h-3 w-3 -mr-0.5" strokeWidth={2.5} />
        ))}
      {displayPercentage}%
    </span>
  )

  // Only wrap in tooltip if comparison date label is provided
  if (comparisonDateLabel) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>{badgeContent}</TooltipTrigger>
        <TooltipContent side="top" className="px-2.5 py-1.5">
          <ComparisonTooltipHeader label={comparisonDateLabel} />
          <div className="flex items-center gap-4 justify-between">
            <span className="text-neutral-100">
              {__('Previous:', 'wp-statistics')} {previousValue ?? '-'}
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
