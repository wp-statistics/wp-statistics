import { Skeleton } from '@/components/ui/skeleton'
import { cn } from '@/lib/utils'

export interface BarListSkeletonProps {
  /** Number of items (default: 5) */
  items?: number
  /** Show icon placeholder (default: false) */
  showIcon?: boolean
  /** Additional CSS classes */
  className?: string
}

/**
 * BarListSkeleton - Loading skeleton for horizontal bar lists
 *
 * Displays placeholder items for horizontal bar list components.
 *
 * @example
 * <BarListSkeleton items={5} />
 * <BarListSkeleton items={5} showIcon />
 */
export function BarListSkeleton({ items = 5, showIcon = false, className }: BarListSkeletonProps) {
  return (
    <div className={cn('space-y-3', className)}>
      {[...Array(items)].map((_, i) => (
        <div key={i} className="flex justify-between items-center">
          <div className="flex items-center gap-2">
            {showIcon && <Skeleton className="h-4 w-4 rounded" />}
            <Skeleton className="h-4 w-32" />
          </div>
          <Skeleton className="h-4 w-16" />
        </div>
      ))}
    </div>
  )
}
