import { Skeleton } from '@/components/ui/skeleton'
import { cn } from '@/lib/utils'

export interface ChartSkeletonProps {
  /** Chart height in pixels (default: 256) */
  height?: number
  /** Show title placeholder (default: true) */
  showTitle?: boolean
  /** Additional CSS classes */
  className?: string
}

/**
 * ChartSkeleton - Loading skeleton for chart components
 *
 * Displays a placeholder for chart areas with optional title.
 *
 * @example
 * <ChartSkeleton height={256} />
 * <ChartSkeleton height={320} showTitle={false} />
 */
export function ChartSkeleton({ height = 256, showTitle = true, className }: ChartSkeletonProps) {
  return (
    <div className={cn('space-y-3', className)}>
      {showTitle && <Skeleton className="h-5 w-32" />}
      <Skeleton className="w-full" style={{ height: `${height}px` }} />
    </div>
  )
}
