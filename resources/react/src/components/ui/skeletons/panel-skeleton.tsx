import type { ReactNode } from 'react'

import { Panel } from '@/components/ui/panel'
import { Skeleton } from '@/components/ui/skeleton'
import { cn } from '@/lib/utils'

export interface PanelSkeletonProps {
  /** Show title area (default: true) */
  showTitle?: boolean
  /** Title width class (default: 'w-28') */
  titleWidth?: string
  /** Custom skeleton content */
  children?: ReactNode
  /** Additional CSS classes */
  className?: string
}

/**
 * PanelSkeleton - Loading skeleton wrapper with panel styling
 *
 * Wraps skeleton content in a Panel component with optional title area.
 * Compose with other skeleton components for complex layouts.
 *
 * @example
 * <PanelSkeleton>
 *   <BarListSkeleton items={5} />
 * </PanelSkeleton>
 *
 * @example
 * <PanelSkeleton showTitle={false}>
 *   <MetricsSkeleton count={8} columns={4} />
 * </PanelSkeleton>
 */
export function PanelSkeleton({
  showTitle = true,
  titleWidth = 'w-28',
  children,
  className,
}: PanelSkeletonProps) {
  return (
    <Panel className={className}>
      {showTitle && (
        <div className="px-4 pt-4 pb-3">
          <Skeleton className={cn('h-5', titleWidth)} />
        </div>
      )}
      <div className={cn('px-4 pb-4', !showTitle && 'pt-4')}>{children}</div>
    </Panel>
  )
}
