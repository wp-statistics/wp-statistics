import { cn } from '@/lib/utils'
import { ChevronUp, ChevronDown } from 'lucide-react'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

export interface HorizontalBarProps {
  icon: React.ReactNode
  label: string
  value: string | number
  percentage: string | number
  isNegative?: boolean
  tooltipTitle?: string
  tooltipSubtitle?: string
}

export function HorizontalBar({
  icon,
  label,
  value,
  percentage,
  isNegative = false,
  tooltipTitle,
  tooltipSubtitle,
}: HorizontalBarProps) {
  const rawPercentage = typeof percentage === 'string' ? parseFloat(percentage) : percentage
  const safePercentage = Number.isFinite(rawPercentage) ? Math.min(Math.max(rawPercentage, 0), 100) : 0

  const content = (
    <div className={cn('relative p-2 bg-white rounded-sm overflow-hidden w-full')}>
      <div className="absolute inset-0 transition-all bg-[#F2F0FF]" style={{ width: `${safePercentage}%` }} />

      <div className="relative flex items-center justify-between gap-4 text-sm">
        <div className="flex items-center gap-2 leading-0">
          <span className="text-2xl leading-0">{icon}</span>
          <span>{label}</span>
        </div>

        <div className="flex items-center gap-3">
          <span className="text-sm text-gray-600">{value}</span>
          <div className="flex items-center">
            <span className={cn('text-neutral-500', isNegative ? 'text-[#D54037]' : 'text-[#196140]')}>
              {isNegative ? <ChevronDown size={16} /> : <ChevronUp size={16} />}
            </span>
            <span className={cn('font-medium', isNegative ? 'text-[#D54037]' : 'text-[#196140]')}>{percentage}%</span>
          </div>
        </div>
      </div>
    </div>
  )

  if (tooltipTitle || tooltipSubtitle) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>{content}</TooltipTrigger>
        <TooltipContent
          showArrow={false}
          className="bg-white text-gray-900 shadow-md border border-neutral-200 p-3 rounded-sm"
          side="top"
        >
          <div className="flex flex-col gap-2">
            <div className="font-medium text-xs">{tooltipTitle}</div>
            <div className="flex items-center gap-1">
              <span className="text-xl leading-0">{icon}</span>
              <span className="text-xs">{label}</span>
            </div>
            <div className="flex items-center gap-4 justify-between text-xs">
              <div className="text-neutral-700">{tooltipSubtitle}</div>
              <div className="flex items-center font-medium">
                <span className={cn(isNegative ? 'text-[#D54037]' : 'text-[#196140]')}>
                  {isNegative ? <ChevronDown size={16} /> : <ChevronUp size={16} />}
                </span>
                <span className={cn(isNegative ? 'text-[#D54037]' : 'text-[#196140]')}>{percentage}%</span>
              </div>
            </div>
          </div>
        </TooltipContent>
      </Tooltip>
    )
  }

  return content
}
