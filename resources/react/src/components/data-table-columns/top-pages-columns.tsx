/**
 * Column definitions for the Top Pages data table.
 * Content-based analytics grouped by page.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { DurationCell, NumericCell, PageCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import { parseDateString } from '@/lib/wp-date'
import type { TopPageRecord } from '@/services/page-insight/get-top-pages'

/**
 * Context identifier for user preferences
 */
export const TOP_PAGES_CONTEXT = 'top_pages'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const TOP_PAGES_DEFAULT_HIDDEN_COLUMNS: string[] = ['viewsPerVisitor', 'bounceRate', 'publishedDate']

/**
 * Columns that show PP comparison by default (to reduce visual clutter)
 * Users can enable/disable comparison per column via column config
 */
export const TOP_PAGES_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Columns that support PP comparison display
 */
export const TOP_PAGES_COMPARABLE_COLUMNS: string[] = [
  'visitors',
  'views',
  'viewsPerVisitor',
  'bounceRate',
  'sessionDuration',
]

/**
 * Column configuration for API column optimization
 */
export const TOP_PAGES_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['page_uri', 'page_title'],
  columnDependencies: {
    page: ['page_uri', 'page_title', 'page_wp_id', 'page_type', 'resource_id'],
    visitors: ['visitors'],
    views: ['views'],
    viewsPerVisitor: ['visitors', 'views'],
    bounceRate: ['bounce_rate'],
    sessionDuration: ['avg_time_on_page'],
    publishedDate: ['published_date'],
  },
  context: TOP_PAGES_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const TOP_PAGES_DEFAULT_API_COLUMNS = getDefaultApiColumns(TOP_PAGES_COLUMN_CONFIG)

/**
 * Top Page data interface for the table
 */
export interface TopPage {
  id: string
  pageUri: string
  pageTitle: string
  pageWpId: number | null
  pageType?: string
  resourceId?: number | string | null
  visitors: number
  views: number
  bounceRate: number
  sessionDuration: number
  publishedDate: Date | null
  // Previous period values for comparison
  previousVisitors?: number
  previousViews?: number
  previousBounceRate?: number
  previousSessionDuration?: number
}

/**
 * Transform API response to TopPage interface
 */
export function transformTopPageData(record: TopPageRecord): TopPage {
  const previous = record.previous

  return {
    id: `page-${record.page_uri}`,
    pageUri: record.page_uri || '/',
    pageTitle: record.page_title || record.page_uri || 'Unknown',
    pageWpId: record.page_wp_id || null,
    pageType: record.page_type,
    resourceId: record.resource_id || null,
    visitors: Number(record.visitors) || 0,
    views: Number(record.views) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    sessionDuration: Math.round(Number(record.avg_time_on_page) || 0),
    publishedDate: record.published_date ? parseDateString(record.published_date) : null,
    // Previous period values (only set if comparison data exists)
    ...(previous && {
      previousVisitors: previous.visitors !== undefined ? Number(previous.visitors) : undefined,
      previousViews: previous.views !== undefined ? Number(previous.views) : undefined,
      previousBounceRate: previous.bounce_rate !== undefined ? Math.round(Number(previous.bounce_rate)) : undefined,
      previousSessionDuration:
        previous.avg_time_on_page !== undefined ? Math.round(Number(previous.avg_time_on_page)) : undefined,
    }),
  }
}

/**
 * Options for creating Top Pages columns
 */
export interface TopPagesColumnsOptions {
  /** Date range comparison label for tooltip (from useComparisonDateLabel) */
  comparisonLabel?: string
  /** Columns that should display PP comparison (defaults to TOP_PAGES_DEFAULT_COMPARISON_COLUMNS) */
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Top Pages table
 */
export function createTopPagesColumns(options: TopPagesColumnsOptions = {}): ColumnDef<TopPage>[] {
  const { comparisonLabel, comparisonColumns = TOP_PAGES_DEFAULT_COMPARISON_COLUMNS } = options

  // Helper to check if a column should show comparison
  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'page',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      cell: ({ row }) => (
        <PageCell
          data={{
            title: row.original.pageTitle,
            url: row.original.pageUri,
            pageType: row.original.pageType,
            pageWpId: row.original.pageWpId,
            resourceId: row.original.resourceId,
          }}
          maxLength={40}
          externalUrl={row.original.pageUri}
        />
      ),
      enableSorting: false,
      meta: {
        title: 'Page',
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'visitors',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.visitors}
          previousValue={showComparison('visitors') ? row.original.previousVisitors : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: 'Visitors',
        priority: 'primary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('visitors'),
      },
    },
    {
      accessorKey: 'views',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.views}
          previousValue={showComparison('views') ? row.original.previousViews : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: 'Views',
        priority: 'primary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('views'),
      },
    },
    {
      accessorKey: 'viewsPerVisitor',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: 90,
      cell: ({ row }) => {
        const vpv = row.original.visitors > 0 ? row.original.views / row.original.visitors : 0
        // Calculate previous views per visitor if both previous values exist and comparison is enabled
        const previousVpv =
          showComparison('viewsPerVisitor') &&
          row.original.previousVisitors !== undefined &&
          row.original.previousViews !== undefined &&
          row.original.previousVisitors > 0
            ? row.original.previousViews / row.original.previousVisitors
            : undefined
        return (
          <NumericCell
            value={vpv}
            decimals={1}
            previousValue={previousVpv}
            comparisonLabel={comparisonLabel}
          />
        )
      },
      meta: {
        title: 'Views/Visitor',
        priority: 'secondary',
        mobileLabel: 'V/Visitor',
        isComparable: true,
        showComparison: showComparison('viewsPerVisitor'),
      },
    },
    {
      accessorKey: 'bounceRate',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.bounceRate,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.bounceRate}
          suffix="%"
          previousValue={showComparison('bounceRate') ? row.original.previousBounceRate : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: 'Bounce Rate',
        priority: 'secondary',
        mobileLabel: 'Bounce',
        isComparable: true,
        showComparison: showComparison('bounceRate'),
      },
    },
    {
      accessorKey: 'sessionDuration',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.duration,
      cell: ({ row }) => (
        <DurationCell
          seconds={row.original.sessionDuration}
          previousSeconds={showComparison('sessionDuration') ? row.original.previousSessionDuration : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: 'Avg. Time on Page',
        priority: 'secondary',
        mobileLabel: 'Time on Page',
        isComparable: true,
        showComparison: showComparison('sessionDuration'),
      },
    },
    {
      accessorKey: 'publishedDate',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: 100,
      cell: ({ row }) => {
        const date = row.original.publishedDate
        if (!date) return <span className="text-xs text-neutral-400">—</span>
        const formattedDate = date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric',
        })
        return <span className="text-xs text-neutral-700 whitespace-nowrap">{formattedDate}</span>
      },
      meta: {
        title: 'Published Date',
        priority: 'secondary',
        mobileLabel: 'Published',
      },
    },
  ]
}
