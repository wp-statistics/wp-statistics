/**
 * Column definitions for the Top Categories data table.
 * Content-based analytics grouped by taxonomy term.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { DurationCell, NumericCell, TermCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import type { TopCategoryRecord } from '@/services/content-analytics/get-top-categories'

/**
 * Context identifier for user preferences
 */
export const TOP_CATEGORIES_CONTEXT = 'top_categories'

/**
 * Columns hidden by default (can be shown via column management)
 * Shows 5 columns by default: term, visitors, views, published, sessionDuration
 */
export const TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS: string[] = ['viewsPerContent', 'bounceRate']

/**
 * Columns that show PP comparison by default (to reduce visual clutter)
 * Users can enable/disable comparison per column via column config
 */
export const TOP_CATEGORIES_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Columns that support PP comparison display
 */
export const TOP_CATEGORIES_COMPARABLE_COLUMNS: string[] = [
  'visitors',
  'views',
  'published',
  'viewsPerContent',
  'bounceRate',
  'sessionDuration',
]

/**
 * Column configuration for API column optimization
 */
export const TOP_CATEGORIES_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['term_id', 'term_name'],
  columnDependencies: {
    term: ['term_id', 'term_name'],
    visitors: ['visitors'],
    views: ['views'],
    published: ['published_content'],
    viewsPerContent: ['views', 'published_content'],
    bounceRate: ['bounce_rate'],
    sessionDuration: ['avg_time_on_page'],
  },
  context: TOP_CATEGORIES_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const TOP_CATEGORIES_DEFAULT_API_COLUMNS = getDefaultApiColumns(TOP_CATEGORIES_COLUMN_CONFIG)

/**
 * Top Category data interface for the table
 */
export interface TopCategory {
  id: string
  termId: number
  termName: string
  visitors: number
  views: number
  published: number
  bounceRate: number
  sessionDuration: number
  // Previous period values for comparison
  previousVisitors?: number
  previousViews?: number
  previousPublished?: number
  previousBounceRate?: number
  previousSessionDuration?: number
}

/**
 * Transform API response to TopCategory interface
 */
export function transformTopCategoryData(record: TopCategoryRecord): TopCategory {
  const previous = record.previous

  return {
    id: `term-${record.term_id}`,
    termId: record.term_id,
    termName: record.term_name || __('Unknown Term', 'wp-statistics'),
    visitors: Number(record.visitors) || 0,
    views: Number(record.views) || 0,
    published: Number(record.published_content) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    sessionDuration: Math.round(Number(record.avg_time_on_page) || 0),
    // Previous period values (only set if comparison data exists)
    ...(previous && {
      previousVisitors: previous.visitors !== undefined ? Number(previous.visitors) : undefined,
      previousViews: previous.views !== undefined ? Number(previous.views) : undefined,
      previousPublished: previous.published_content !== undefined ? Number(previous.published_content) : undefined,
      previousBounceRate: previous.bounce_rate !== undefined ? Math.round(Number(previous.bounce_rate)) : undefined,
      previousSessionDuration:
        previous.avg_time_on_page !== undefined ? Math.round(Number(previous.avg_time_on_page)) : undefined,
    }),
  }
}

/**
 * Options for creating Top Categories columns
 */
export interface TopCategoriesColumnsOptions {
  /** Date range comparison label for tooltip (from useComparisonDateLabel) */
  comparisonLabel?: string
  /** Columns that should display PP comparison (defaults to TOP_CATEGORIES_DEFAULT_COMPARISON_COLUMNS) */
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Top Categories table
 */
export function createTopCategoriesColumns(options: TopCategoriesColumnsOptions = {}): ColumnDef<TopCategory>[] {
  const { comparisonLabel, comparisonColumns = TOP_CATEGORIES_DEFAULT_COMPARISON_COLUMNS } = options

  // Helper to check if a column should show comparison
  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'term',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      cell: ({ row }) => (
        <TermCell
          termId={row.original.termId}
          termName={row.original.termName}
        />
      ),
      enableSorting: false,
      meta: {
        title: __('Term Name', 'wp-statistics'),
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
        title: __('Visitors', 'wp-statistics'),
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
        title: __('Views', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('views'),
      },
    },
    {
      accessorKey: 'published',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.published}
          previousValue={showComparison('published') ? row.original.previousPublished : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: __('Published', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('published'),
      },
    },
    {
      accessorKey: 'viewsPerContent',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: 90,
      cell: ({ row }) => {
        const vpc = row.original.published > 0 ? row.original.views / row.original.published : 0
        // Calculate previous views per content if both previous values exist and comparison is enabled
        const previousVpc =
          showComparison('viewsPerContent') &&
          row.original.previousPublished !== undefined &&
          row.original.previousViews !== undefined &&
          row.original.previousPublished > 0
            ? row.original.previousViews / row.original.previousPublished
            : undefined
        return (
          <NumericCell
            value={vpc}
            decimals={1}
            previousValue={previousVpc}
            comparisonLabel={comparisonLabel}
          />
        )
      },
      enableSorting: false,
      meta: {
        title: __('Views/Content', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('V/Content', 'wp-statistics'),
        isComparable: true,
        showComparison: showComparison('viewsPerContent'),
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
        title: __('Bounce Rate', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('Bounce', 'wp-statistics'),
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
        title: __('Avg. Time on Page', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('Time on Page', 'wp-statistics'),
        isComparable: true,
        showComparison: showComparison('sessionDuration'),
      },
    },
  ]
}
