/**
 * Composable Skeleton Components
 *
 * Reusable skeleton building blocks for loading states.
 * Compose these components to create page-specific loading UIs.
 *
 * @example
 * import {
 *   MetricsSkeleton,
 *   ChartSkeleton,
 *   BarListSkeleton,
 *   TableSkeleton,
 *   PanelSkeleton
 * } from '@/components/ui/skeletons'
 *
 * // Compose for a page layout
 * <PanelSkeleton>
 *   <MetricsSkeleton count={8} columns={4} />
 * </PanelSkeleton>
 * <PanelSkeleton>
 *   <ChartSkeleton height={256} />
 * </PanelSkeleton>
 */

export { MetricsSkeleton, type MetricsSkeletonProps } from './metrics-skeleton'
export { ChartSkeleton, type ChartSkeletonProps } from './chart-skeleton'
export { BarListSkeleton, type BarListSkeletonProps } from './bar-list-skeleton'
export { TableSkeleton, type TableSkeletonProps } from './table-skeleton'
export { PanelSkeleton, type PanelSkeletonProps } from './panel-skeleton'
