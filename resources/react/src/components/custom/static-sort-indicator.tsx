import { ArrowDownWideNarrow, ArrowUpNarrowWide } from 'lucide-react'

import { cn } from '@/lib/utils'

interface StaticSortIndicatorProps {
  title: string
  direction: 'asc' | 'desc'
  className?: string
}

/**
 * A simple, non-interactive sort indicator for columns with pre-sorted data.
 * Used in overview widgets where data is already sorted server-side.
 */
export function StaticSortIndicator({ title, direction, className }: StaticSortIndicatorProps) {
  const Icon = direction === 'desc' ? ArrowDownWideNarrow : ArrowUpNarrowWide

  return (
    <span className={cn('inline-flex items-center gap-1', className)}>
      {title}
      <Icon className="size-3.5 text-muted-foreground" />
    </span>
  )
}
