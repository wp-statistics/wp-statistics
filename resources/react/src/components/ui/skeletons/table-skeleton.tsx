import { Skeleton } from '@/components/ui/skeleton'
import { cn } from '@/lib/utils'

export interface TableSkeletonProps {
  /** Number of rows (default: 5) */
  rows?: number
  /** Number of columns (default: 4) */
  columns?: number
  /** Show header row (default: true) */
  showHeader?: boolean
  /** Additional CSS classes */
  className?: string
}

/**
 * TableSkeleton - Loading skeleton for data tables
 *
 * Displays placeholder rows for table components.
 *
 * @example
 * <TableSkeleton rows={10} columns={5} />
 * <TableSkeleton rows={5} showHeader={false} />
 */
export function TableSkeleton({ rows = 5, columns = 4, showHeader = true, className }: TableSkeletonProps) {
  return (
    <div className={cn('space-y-2', className)}>
      {showHeader && (
        <div className="flex gap-4 pb-2 border-b border-neutral-100">
          {[...Array(columns)].map((_, i) => (
            <Skeleton key={i} className="h-4 flex-1" />
          ))}
        </div>
      )}
      {[...Array(rows)].map((_, rowIndex) => (
        <div key={rowIndex} className="flex gap-4 py-2">
          {[...Array(columns)].map((_, colIndex) => (
            <Skeleton key={colIndex} className={cn('h-4 flex-1', colIndex === 0 && 'max-w-[200px]')} />
          ))}
        </div>
      ))}
    </div>
  )
}
