import { ChevronDown, ChevronUp } from 'lucide-react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { cn } from '@/lib/utils'

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
  // Use fillPercentage for bar width (proportion of total), default to 0 if not provided
  const barWidth = fillPercentage !== undefined ? Math.min(Math.max(fillPercentage, 0), 100) : 0

  const content = (
    <div className={cn('relative p-2 bg-white rounded-sm overflow-hidden w-full')}>
      <div className="absolute inset-0 transition-all bg-[#F2F0FF]" style={{ width: `${barWidth}%` }} />

      <div className="relative flex items-center justify-between gap-4">
        <div className="flex items-center gap-2 leading-0">
          {icon && <span className="text-xl leading-none">{icon}</span>}
          <span className="text-xs font-medium text-neutral-700">{label}</span>
        </div>

        <div className="flex items-center gap-3">
          <span className={cn('text-xs tabular-nums', isFirst ? 'font-semibold text-neutral-900' : 'font-medium text-neutral-600')}>{value}</span>
          <div className="flex items-center">
            <span className={isNegative ? 'text-red-600' : 'text-emerald-600'}>
              {isNegative ? <ChevronDown className="h-3.5 w-3.5" strokeWidth={2.5} /> : <ChevronUp className="h-3.5 w-3.5" strokeWidth={2.5} />}
            </span>
            <span className={cn('font-medium text-xs tabular-nums', isNegative ? 'text-red-600' : 'text-emerald-600')}>
              {percentage}%
            </span>
          </div>
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
              <span className="text-neutral-300">{label}</span>
            </div>
            <div className="flex items-center gap-4 justify-between border-t border-neutral-700 pt-1.5 mt-0.5">
              <span className="text-neutral-400">{tooltipSubtitle}</span>
              <div className="flex items-center font-medium">
                <span className={isNegative ? 'text-red-400' : 'text-emerald-400'}>
                  {isNegative ? <ChevronDown className="h-3.5 w-3.5" strokeWidth={2.5} /> : <ChevronUp className="h-3.5 w-3.5" strokeWidth={2.5} />}
                </span>
                <span className={cn('tabular-nums', isNegative ? 'text-red-400' : 'text-emerald-400')}>{percentage}%</span>
              </div>
            </div>
          </div>
        </TooltipContent>
      </Tooltip>
    )
  }

  return content
}
