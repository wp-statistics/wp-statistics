/**
 * Column definitions for the Top Pages data table.
 * Content-based analytics grouped by page.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { DurationCell, NumericCell, PageCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import type { TopPageRecord } from '@/services/page-insight/get-top-pages'

/**
 * Context identifier for user preferences
 */
export const TOP_PAGES_CONTEXT = 'top_pages_data_table'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const TOP_PAGES_DEFAULT_HIDDEN_COLUMNS: string[] = ['viewsPerVisitor', 'bounceRate']

/**
 * Column configuration for API column optimization
 */
export const TOP_PAGES_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['page_uri', 'page_title'],
  columnDependencies: {
    page: ['page_uri', 'page_title', 'page_wp_id'],
    visitors: ['visitors'],
    views: ['views'],
    viewsPerVisitor: ['visitors', 'views'],
    bounceRate: ['bounce_rate'],
    sessionDuration: ['avg_time_on_page'],
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
  visitors: number
  views: number
  bounceRate: number
  sessionDuration: number
}

/**
 * Transform API response to TopPage interface
 */
export function transformTopPageData(record: TopPageRecord): TopPage {
  return {
    id: `page-${record.page_uri}`,
    pageUri: record.page_uri || '/',
    pageTitle: record.page_title || record.page_uri || 'Unknown',
    pageWpId: record.page_wp_id || null,
    visitors: Number(record.visitors) || 0,
    views: Number(record.views) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    sessionDuration: Math.round(Number(record.avg_time_on_page) || 0),
  }
}

/**
 * Create column definitions for the Top Pages table
 */
export function createTopPagesColumns(): ColumnDef<TopPage>[] {
  return [
    {
      accessorKey: 'page',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Page" />,
      cell: ({ row }) => (
        <PageCell
          data={{
            title: row.original.pageTitle,
            url: row.original.pageUri,
          }}
          maxLength={40}
          externalUrl={row.original.pageUri}
        />
      ),
      enableSorting: false,
      meta: {
        priority: 'primary',
        cardPosition: 'header',
        mobileLabel: 'Page',
      },
    },
    {
      accessorKey: 'visitors',
      header: ({ column, table }) => (
        <DataTableColumnHeader column={column} table={table} title="Visitors" className="text-right" />
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.visitors} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: 'Visitors',
      },
    },
    {
      accessorKey: 'views',
      header: ({ column, table }) => (
        <DataTableColumnHeader column={column} table={table} title="Views" className="text-right" />
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.views} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: 'Views',
      },
    },
    {
      accessorKey: 'viewsPerVisitor',
      header: ({ column, table }) => (
        <DataTableColumnHeader column={column} table={table} title="Views/Visitor" className="text-right" />
      ),
      size: 90,
      cell: ({ row }) => {
        const vpv = row.original.visitors > 0 ? row.original.views / row.original.visitors : 0
        return <NumericCell value={vpv} decimals={1} />
      },
      meta: {
        priority: 'secondary',
        mobileLabel: 'V/Visitor',
      },
    },
    {
      accessorKey: 'bounceRate',
      header: ({ column, table }) => (
        <DataTableColumnHeader column={column} table={table} title="Bounce Rate" className="text-right" />
      ),
      size: COLUMN_SIZES.bounceRate,
      cell: ({ row }) => <NumericCell value={row.original.bounceRate} suffix="%" />,
      meta: {
        priority: 'secondary',
        mobileLabel: 'Bounce',
      },
    },
    {
      accessorKey: 'sessionDuration',
      header: ({ column, table }) => (
        <DataTableColumnHeader column={column} table={table} title="Avg. Time on Page" className="text-right" />
      ),
      size: COLUMN_SIZES.duration,
      cell: ({ row }) => <DurationCell seconds={row.original.sessionDuration} />,
      meta: {
        priority: 'secondary',
        mobileLabel: 'Time on Page',
      },
    },
  ]
}
