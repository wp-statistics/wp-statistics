import { ChevronDown, ChevronUp, Info } from 'lucide-react'
import * as React from 'react'

import { Badge } from '@/components/ui/badge'
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

  const totalMetrics = displayMetrics.length
  const totalRows = Math.ceil(totalMetrics / columns)

  const getPositionClasses = (index: number) => {
    const row = Math.floor(index / columns)
    const col = index % columns
    const isFirstRow = row === 0
    const isLastCol = col === columns - 1

    // Internal dividers only - outer border comes from Panel wrapper
    // Right border on all except last column (vertical dividers)
    // Top border on all except first row (horizontal dividers)
    return cn(
      !isFirstRow && 'border-t border-neutral-200',
      !isLastCol && 'border-r border-neutral-200'
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
    <div className={cn('bg-white p-4 flex flex-col gap-2', positionClasses)}>
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-1.5">
          <span className="text-xs font-medium text-neutral-500 uppercase tracking-wide">{label}</span>
          {tooltipContent && (
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="text-neutral-400 hover:text-neutral-500 transition-colors" type="button">
                  <Info className="h-3.5 w-3.5" />
                </button>
              </TooltipTrigger>
              <TooltipContent showArrow={false} side="top">
                {tooltipContent}
              </TooltipContent>
            </Tooltip>
          )}
        </div>
        {icon && <div className="shrink-0">{icon}</div>}
      </div>

      <div className="flex items-baseline gap-2">
        <span className="text-2xl font-semibold text-neutral-800 leading-none tabular-nums">{value}</span>
        {hasPercentage && (
          <Badge
            className={cn(
              'flex items-center py-0.5 px-1.5 rounded gap-0.5 shadow-none',
              isZero
                ? 'bg-neutral-100 text-neutral-600 hover:bg-neutral-100'
                : isNegative
                  ? 'bg-red-50 text-red-600 hover:bg-red-50'
                  : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-50'
            )}
          >
            {!isZero && (isNegative ? <ChevronDown className="h-3.5 w-3.5" strokeWidth={2.5} /> : <ChevronUp className="h-3.5 w-3.5" strokeWidth={2.5} />)}
            <span className="font-medium text-xs tabular-nums">{percentage}%</span>
          </Badge>
        )}
      </div>
    </div>
  )
}
