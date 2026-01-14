/**
 * Column definitions for the Author Pages data table.
 * Shows author archive pages with view counts.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { StaticSortIndicator } from '@/components/custom/static-sort-indicator'
import { NumericCell, PageCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import type { AuthorPageRecord } from '@/services/page-insight/get-author-pages'

/**
 * Author Page data interface for the table
 */
export interface AuthorPage {
  id: string
  pageUri: string
  authorName: string
  views: number
}

/**
 * Transform API response to AuthorPage interface
 */
export function transformAuthorPageData(record: AuthorPageRecord): AuthorPage {
  return {
    id: `author-${record.page_uri}`,
    pageUri: record.page_uri || '/',
    authorName: record.page_title || 'Unknown Author',
    views: Number(record.views) || 0,
  }
}

/**
 * Create column definitions for the Author Pages table
 */
export function createAuthorPagesColumns(): ColumnDef<AuthorPage>[] {
  return [
    {
      accessorKey: 'author',
      header: __('Author', 'wp-statistics'),
      cell: ({ row }) => (
        <PageCell
          data={{
            title: row.original.authorName,
            url: row.original.pageUri,
          }}
          maxLength={40}
          externalUrl={row.original.pageUri}
        />
      ),
      enableSorting: false,
      meta: {
        title: 'Author',
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
        title: 'Views',
        priority: 'primary',
        cardPosition: 'body',
      },
    },
  ]
}
