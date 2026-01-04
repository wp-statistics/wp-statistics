import { Skeleton } from '@/components/ui/skeleton'
import { cn } from '@/lib/utils'

export interface MetricsSkeletonProps {
  /** Number of metric items (default: 4) */
  count?: number
  /** Grid columns (default: 4) */
  columns?: 1 | 2 | 3 | 4 | 6
  /** Additional CSS classes */
  className?: string
}

/**
 * MetricsSkeleton - Loading skeleton for metrics grids
 *
 * Displays a grid of placeholder metric items with label and value.
 *
 * @example
 * <MetricsSkeleton count={8} columns={4} />
 */
export function MetricsSkeleton({ count = 4, columns = 4, className }: MetricsSkeletonProps) {
  const gridColsClass = {
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    6: 'grid-cols-6',
  }[columns]

  return (
    <div className={cn('grid gap-4', gridColsClass, className)}>
      {[...Array(count)].map((_, i) => (
        <div key={i} className="space-y-2">
          <Skeleton className="h-4 w-24" />
          <Skeleton className="h-8 w-32" />
        </div>
      ))}
    </div>
  )
}
