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
    const isLastRow = row === totalRows - 1
    const isFirstCol = col === 0
    const isLastCol = col === columns - 1

    // Apply rounded corners only to corner positions
    // Remove right border for all except last column
    // Remove bottom border for all except last row
    return cn(
      'border-t border-l border-neutral-200',
      isLastCol && 'border-r',
      isLastRow && 'border-b',
      isFirstRow && isFirstCol && 'rounded-tl-xl',
      isFirstRow && isLastCol && 'rounded-tr-xl',
      isLastRow && isFirstCol && 'rounded-bl-xl',
      isLastRow && isLastCol && 'rounded-br-xl'
    )
  }

  return (
    <div className={cn('grid gap-0 w-full overflow-hidden rounded-xl', gridColsClass, className)}>
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

  return (
    <div className={cn('bg-white p-4 flex flex-col gap-3', positionClasses)}>
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-1.5">
          <span className="text-xs font-medium text-neutral-500 uppercase tracking-wide">{label}</span>
          {tooltipContent && (
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="text-neutral-500 hover:text-neutral-600 transition-colors" type="button">
                  <Info size={14} />
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

      <div className="flex items-end gap-2">
        <div className="text-2xl font-normal text-card-foreground leading-none">{value}</div>
        {hasPercentage && (
          <Badge
            className={cn(
              'flex items-center py-0.5 px-1 rounded gap-1 shadow-none',
              isNegative ? 'bg-red-50 text-red-800' : 'bg-emerald-50 text-emerald-800'
            )}
          >
            {isNegative ? <ChevronDown size={16} strokeWidth={3} /> : <ChevronUp size={16} strokeWidth={3} />}
            <span className="font-medium text-sm">{percentage}%</span>
          </Badge>
        )}
      </div>
    </div>
  )
}
