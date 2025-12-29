import { ChevronDown, ChevronUp, Info } from 'lucide-react'
import * as React from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
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
  const displayMetrics = metrics.slice(0, 12)

  const gridColsClass = {
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    6: 'grid-cols-6',
    12: 'grid-cols-12',
  }[columns]

  const getPositionClasses = (index: number) => {
    const row = Math.floor(index / columns)
    const col = index % columns
    const isFirstRow = row === 0
    const isLastCol = col === columns - 1

    return cn(
      !isFirstRow && 'border-t border-neutral-100',
      !isLastCol && 'border-r border-neutral-100'
    )
  }

  return (
    <div className={cn('grid gap-0 w-full overflow-hidden', gridColsClass, className)}>
      {displayMetrics.map((metric, index) => (
        <MetricCard key={`${metric.label}-${index}`} {...metric} positionClasses={getPositionClasses(index)} />
      ))}
    </div>
  )
}

interface MetricCardProps extends MetricItem {
  positionClasses?: string
}

function MetricCard({
  label,
  value,
  percentage,
  isNegative = false,
  icon,
  tooltipContent,
  positionClasses,
}: MetricCardProps) {
  const hasPercentage = percentage !== undefined && percentage !== null
  const percentageNum = typeof percentage === 'string' ? parseFloat(percentage) : percentage
  const isZero = percentageNum === 0

  return (
    <div
      className={cn(
        'bg-white px-5 py-4 flex flex-col justify-between min-h-[76px]',
        'transition-colors duration-150',
        'hover:bg-neutral-50/50',
        positionClasses
      )}
    >
      {/* Label row with tooltip */}
      <div className="flex items-center gap-1">
        <span className="text-[11px] font-medium text-neutral-400 uppercase tracking-[0.04em] leading-none">
          {label}
        </span>
        {tooltipContent && (
          <Tooltip>
            <TooltipTrigger asChild>
              <button
                className="text-neutral-300 hover:text-neutral-400 transition-colors -mt-px"
                type="button"
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
        <span className="text-xl font-medium text-neutral-800 leading-none tabular-nums">
          {value}
        </span>
        {hasPercentage && (
          <span
            className={cn(
              'inline-flex items-center gap-0.5 text-[11px] font-medium tabular-nums leading-none',
              isZero
                ? 'text-neutral-400'
                : isNegative
                  ? 'text-red-500'
                  : 'text-emerald-500'
            )}
          >
            {!isZero && (
              isNegative
                ? <ChevronDown className="h-3 w-3 -mr-0.5" strokeWidth={2.5} />
                : <ChevronUp className="h-3 w-3 -mr-0.5" strokeWidth={2.5} />
            )}
            {percentage}%
          </span>
        )}
      </div>
    </div>
  )
}
