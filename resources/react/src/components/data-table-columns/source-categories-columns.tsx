/**
 * Column definitions for the Source Categories data table.
 * Shows traffic grouped by source category (channel).
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { DurationCell, NumericCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'

/**
 * Context identifier for user preferences
 */
export const SOURCE_CATEGORIES_CONTEXT = 'source-categories'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const SOURCE_CATEGORIES_DEFAULT_HIDDEN_COLUMNS: string[] = ['sessionDuration', 'bounceRate', 'pagesPerSession']

/**
 * Columns that show PP comparison by default (to reduce visual clutter)
 * Users can enable/disable comparison per column via column config
 */
export const SOURCE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Columns that support PP comparison display
 */
export const SOURCE_CATEGORIES_COMPARABLE_COLUMNS: string[] = ['visitors', 'views', 'sessionDuration', 'bounceRate', 'pagesPerSession']

/**
 * Column configuration for API column optimization
 */
export const SOURCE_CATEGORIES_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['referrer_channel'],
  columnDependencies: {
    sourceCategory: ['referrer_channel'],
    visitors: ['visitors'],
    views: ['views'],
    sessionDuration: ['avg_session_duration'],
    bounceRate: ['bounce_rate'],
    pagesPerSession: ['pages_per_session'],
  },
  context: SOURCE_CATEGORIES_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const SOURCE_CATEGORIES_DEFAULT_API_COLUMNS = getDefaultApiColumns(SOURCE_CATEGORIES_COLUMN_CONFIG)

/**
 * API response record for a source category
 */
export interface SourceCategoryRecord {
  referrer_channel: string
  visitors: number
  views: number
  avg_session_duration: number
  bounce_rate: number
  pages_per_session: number
  previous?: {
    visitors?: number
    views?: number
    avg_session_duration?: number
    bounce_rate?: number
    pages_per_session?: number
  }
}

/**
 * Source category data interface for the table
 */
export interface SourceCategory {
  id: string
  sourceCategory: string      // Display name (e.g., "Organic Search")
  channelKey: string          // Raw key (e.g., "search")
  visitors: number
  views: number
  sessionDuration: number
  bounceRate: number
  pagesPerSession: number
  // Previous period values for comparison
  previousVisitors?: number
  previousViews?: number
  previousSessionDuration?: number
  previousBounceRate?: number
  previousPagesPerSession?: number
}

/**
 * Map of channel keys to display names
 * These match the SourceChannels::getList() from PHP
 */
export const SOURCE_CHANNEL_NAMES: Record<string, string> = {
  direct: 'Direct Traffic',
  search: 'Organic Search',
  paid_search: 'Paid Search',
  affiliates: 'Affiliates',
  audio: 'Audio',
  display: 'Display',
  email: 'Email',
  mobile_notification: 'Mobile Notification',
  shopping: 'Organic Shopping',
  social: 'Organic Social',
  video: 'Organic Video',
  paid_shopping: 'Paid Shopping',
  paid_social: 'Paid Social',
  paid_video: 'Paid Video',
  paid_other: 'Paid Other',
  referral: 'Referral',
  sms: 'SMS',
  unassigned: 'Unassigned Traffic',
}

/**
 * Get display name for a channel key
 */
export function getChannelDisplayName(channelKey: string): string {
  return SOURCE_CHANNEL_NAMES[channelKey] || channelKey
}

/**
 * Transform API response to SourceCategory interface
 */
export function transformSourceCategoryData(record: SourceCategoryRecord): SourceCategory {
  const previous = record.previous
  const channelKey = record.referrer_channel || 'unassigned'

  return {
    id: `source-category-${channelKey}`,
    sourceCategory: getChannelDisplayName(channelKey),
    channelKey,
    visitors: Number(record.visitors) || 0,
    views: Number(record.views) || 0,
    sessionDuration: Number(record.avg_session_duration) || 0,
    bounceRate: Number(record.bounce_rate) || 0,
    pagesPerSession: Number(record.pages_per_session) || 0,
    // Previous period values (only set if comparison data exists)
    ...(previous && {
      previousVisitors: previous.visitors !== undefined ? Number(previous.visitors) : undefined,
      previousViews: previous.views !== undefined ? Number(previous.views) : undefined,
      previousSessionDuration: previous.avg_session_duration !== undefined ? Number(previous.avg_session_duration) : undefined,
      previousBounceRate: previous.bounce_rate !== undefined ? Number(previous.bounce_rate) : undefined,
      previousPagesPerSession: previous.pages_per_session !== undefined ? Number(previous.pages_per_session) : undefined,
    }),
  }
}

/**
 * Options for creating Source Categories columns
 */
export interface SourceCategoriesColumnsOptions {
  /** Date range comparison label for tooltip (from useComparisonDateLabel) */
  comparisonLabel?: string
  /** Columns that should display PP comparison (defaults to SOURCE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS) */
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Source Categories table
 */
export function createSourceCategoriesColumns(options: SourceCategoriesColumnsOptions = {}): ColumnDef<SourceCategory>[] {
  const { comparisonLabel, comparisonColumns = SOURCE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS } = options

  // Helper to check if a column should show comparison
  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'sourceCategory',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: 180,
      cell: ({ row }) => (
        <span className="text-xs font-medium text-neutral-700">{row.original.sourceCategory}</span>
      ),
      enableSorting: false,
      meta: {
        title: 'Source Category',
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
        title: 'Avg. Duration',
        priority: 'secondary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('sessionDuration'),
      },
    },
    {
      accessorKey: 'bounceRate',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.bounceRate,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.bounceRate}
          previousValue={showComparison('bounceRate') ? row.original.previousBounceRate : undefined}
          comparisonLabel={comparisonLabel}
          suffix="%"
        />
      ),
      meta: {
        title: 'Bounce Rate',
        priority: 'secondary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('bounceRate'),
      },
    },
    {
      accessorKey: 'pagesPerSession',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.viewsPerSession,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.pagesPerSession}
          previousValue={showComparison('pagesPerSession') ? row.original.previousPagesPerSession : undefined}
          comparisonLabel={comparisonLabel}
          decimals={1}
        />
      ),
      meta: {
        title: 'Pages/Session',
        priority: 'secondary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('pagesPerSession'),
      },
    },
  ]
}
