import { ChevronDown, ChevronUp, Info } from 'lucide-react'
import * as React from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { useBreakpoint } from '@/hooks/use-breakpoint'
import { cn } from '@/lib/utils'

export interface MetricItem {
  label: string
  value: string | number
  percentage?: string | number
  isNegative?: boolean
  icon?: React.ReactNode
  tooltipContent?: string
}

export interface MetricsProps {
  metrics: MetricItem[]
  columns?: 1 | 2 | 3 | 4 | 6 | 12
  className?: string
}

export function Metrics({ metrics, columns = 3, className }: MetricsProps) {
  const { isMobile, isTablet } = useBreakpoint()
  const displayMetrics = metrics.slice(0, 12)

  // Auto-scale columns based on breakpoint
  // Desktop: columns prop value
  // Tablet: min(columns, 3)
  // Mobile: min(columns, 2)
  const responsiveColumns = React.useMemo(() => {
    if (isMobile) return Math.min(columns, 2) as 1 | 2
    if (isTablet) return Math.min(columns, 3) as 1 | 2 | 3
    return columns
  }, [isMobile, isTablet, columns])

  const gridColsClass = {
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    6: 'grid-cols-6',
    12: 'grid-cols-12',
  }[responsiveColumns]

  const getPositionClasses = (index: number) => {
    const row = Math.floor(index / responsiveColumns)
    const col = index % responsiveColumns
    const isFirstRow = row === 0
    const isLastCol = col === responsiveColumns - 1

    return cn(
      !isFirstRow && 'border-t border-neutral-100',
      !isLastCol && 'border-r border-neutral-100'
    )
  }

  return (
    <div className={cn('grid gap-0 w-full overflow-hidden', gridColsClass, className)}>
      {displayMetrics.map((metric, index) => (
        <MetricCard
          key={`${metric.label}-${index}`}
          {...metric}
          positionClasses={getPositionClasses(index)}
          isMobile={isMobile}
        />
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
  positionClasses,
  isMobile,
}: MetricCardProps) {
  const hasPercentage = percentage !== undefined && percentage !== null
  const percentageNum = typeof percentage === 'string' ? parseFloat(percentage) : percentage
  const isZero = percentageNum === 0
  // Don't show decimals when percentage >= 100
  const displayPercentage = percentageNum !== undefined && percentageNum >= 100
    ? Math.round(percentageNum)
    : percentage

  return (
    <div
      className={cn(
        'bg-white flex flex-col justify-between',
        // Responsive padding
        'px-3 py-3 md:px-4 md:py-3.5 lg:px-5 lg:py-4',
        // Responsive min-height
        'min-h-[68px] md:min-h-[72px] lg:min-h-[76px]',
        'transition-colors duration-150',
        'hover:bg-neutral-50/50',
        positionClasses
      )}
    >
      {/* Label row with tooltip */}
      <div className="flex items-center gap-1">
        <span
          className={cn(
            'font-medium text-neutral-400 uppercase tracking-[0.04em] leading-none',
            // Responsive text size
            'text-[10px] md:text-[11px]'
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
      <div className="flex items-baseline gap-2 mt-1.5">
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
          <span
            className={cn(
              'inline-flex items-center gap-0.5 font-medium tabular-nums leading-none',
              // Responsive text size
              'text-[10px] md:text-[11px]',
              isZero
                ? 'text-neutral-400'
                : isNegative
                  ? 'text-red-600'
                  : 'text-emerald-600'
            )}
          >
            {!isZero && (
              isNegative
                ? <ChevronDown className="h-3 w-3 -mr-0.5" strokeWidth={2.5} />
                : <ChevronUp className="h-3 w-3 -mr-0.5" strokeWidth={2.5} />
            )}
            {displayPercentage}%
          </span>
        )}
      </div>
    </div>
  )
}
