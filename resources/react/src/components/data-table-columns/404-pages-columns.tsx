/**
 * Column definitions for the 404 Pages data table.
 * Displays not-found page URIs and their view counts.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { StaticSortIndicator } from '@/components/custom/static-sort-indicator'
import { NumericCell, UriCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import type { NotFoundPageRecord } from '@/services/page-insight/get-404-pages'

/**
 * Context identifier for user preferences
 */
export const NOT_FOUND_PAGES_CONTEXT = '404_pages'

/**
 * 404 page data interface for the table
 */
export interface NotFoundPage {
  id: string
  pageUri: string
  views: number
}

/**
 * Transform API response to NotFoundPage interface
 */
export function transformNotFoundPageData(record: NotFoundPageRecord): NotFoundPage {
  return {
    id: `404-${record.page_uri}`,
    pageUri: record.page_uri || '/',
    views: Number(record.views) || 0,
  }
}

/**
 * Create column definitions for the 404 Pages table
 */
export function createNotFoundPagesColumns(): ColumnDef<NotFoundPage>[] {
  return [
    {
      accessorKey: 'pageUri',
      header: __('URL', 'wp-statistics'),
      cell: ({ row }) => <UriCell uri={row.original.pageUri} />,
      enableSorting: false,
      meta: {
        title: __('URL', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'views',
      header: () => (
        <div className="text-right">
          <StaticSortIndicator title={__('Views', 'wp-statistics')} direction="desc" />
        </div>
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.views} />,
      enableSorting: false,
      meta: {
        title: __('Views', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
      },
    },
  ]
}
