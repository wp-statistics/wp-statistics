import { cn } from '@/lib/utils'

interface ComparisonTooltipHeaderProps {
  /** Pre-formatted label from useComparisonDateLabel() */
  label?: string
  /** Optional className for styling */
  className?: string
}

/**
 * Renders a date range comparison header inside tooltips
 *
 * @example
 * ```tsx
 * <TooltipContent>
 *   <ComparisonTooltipHeader label="Dec 16, 2025 - Jan 12, 2026 vs. Nov 18, 2025 - Dec 15, 2025" />
 *   <div>Previous: 5,000</div>
 * </TooltipContent>
 * ```
 */
export function ComparisonTooltipHeader({ label, className }: ComparisonTooltipHeaderProps) {
  if (!label) return null

  return (
    <div className={cn('text-[11px] text-neutral-400 border-b border-neutral-700 pb-1.5 mb-1.5', className)}>
      {label}
    </div>
  )
}
