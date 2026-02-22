/**
 * Column definitions for the Referrers data table.
 * Shows all traffic sources grouped by referrer domain.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { DurationCell, NumericCell, ReferrerCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import type { ReferrerRecord } from '@/services/referrals/get-referrers'

/**
 * Context identifier for user preferences
 */
export const REFERRERS_CONTEXT = 'referrers'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const REFERRERS_DEFAULT_HIDDEN_COLUMNS: string[] = ['sessionDuration', 'bounceRate', 'pagesPerSession']

/**
 * Columns that show PP comparison by default (to reduce visual clutter)
 * Users can enable/disable comparison per column via column config
 */
export const REFERRERS_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Columns that support PP comparison display
 */
export const REFERRERS_COMPARABLE_COLUMNS: string[] = ['visitors', 'views', 'sessionDuration', 'bounceRate', 'pagesPerSession']

/**
 * Column configuration for API column optimization
 */
export const REFERRERS_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['referrer_id', 'referrer_domain', 'referrer_name', 'referrer_channel'],
  columnDependencies: {
    domain: ['referrer_domain', 'referrer_channel'],
    name: ['referrer_name'],
    visitors: ['visitors'],
    views: ['views'],
    sessionDuration: ['avg_session_duration'],
    bounceRate: ['bounce_rate'],
    pagesPerSession: ['pages_per_session'],
  },
  context: REFERRERS_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const REFERRERS_DEFAULT_API_COLUMNS = getDefaultApiColumns(REFERRERS_COLUMN_CONFIG)

/**
 * Referrer data interface for the table
 */
export interface Referrer {
  id: string
  domain: string
  name: string
  channel: string
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
 * Transform API response to Referrer interface
 */
export function transformReferrerData(record: ReferrerRecord): Referrer {
  const previous = record.previous

  return {
    id: `referrer-${record.referrer_id}`,
    domain: record.referrer_domain || '',
    name: record.referrer_name || record.referrer_domain || '',
    channel: record.referrer_channel || 'referral',
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
 * Options for creating Referrers columns
 */
export interface ReferrersColumnsOptions {
  /** Date range comparison label for tooltip (from useComparisonDateLabel) */
  comparisonLabel?: string
  /** Columns that should display PP comparison (defaults to REFERRERS_DEFAULT_COMPARISON_COLUMNS) */
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Referrers table
 */
export function createReferrersColumns(options: ReferrersColumnsOptions = {}): ColumnDef<Referrer>[] {
  const { comparisonLabel, comparisonColumns = REFERRERS_DEFAULT_COMPARISON_COLUMNS } = options

  // Helper to check if a column should show comparison
  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'domain',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.referrer,
      cell: ({ row }) => (
        <ReferrerCell
          data={{
            domain: row.original.domain,
            category: row.original.channel,
          }}
          maxLength={30}
        />
      ),
      enableSorting: false,
      meta: {
        title: 'Domain',
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'name',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: 120,
      cell: ({ row }) => (
        <span className="text-xs font-medium text-neutral-700">{row.original.name || '—'}</span>
      ),
      enableSorting: false,
      meta: {
        title: 'Source Name',
        priority: 'secondary',
        cardPosition: 'body',
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
