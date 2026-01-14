/**
 * Generic column definitions for Page + Views data tables.
 * Reusable across Author Pages, Category Pages, and similar reports.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { StaticSortIndicator } from '@/components/custom/static-sort-indicator'
import { NumericCell, PageCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'

/**
 * Generic page data interface for simple page + views tables
 */
export interface PageViewsData {
  id: string
  pageUri: string
  pageTitle: string
  views: number
}

/**
 * Generic API record interface
 */
export interface PageViewsRecord {
  page_uri: string
  page_title: string
  views: number
}

/**
 * Options for customizing the page column
 */
export interface PageViewsColumnOptions {
  /** Header text for the page column (e.g., "Author", "Category", "Page") */
  pageColumnHeader?: string
  /** Max length for truncating the title */
  maxTitleLength?: number
  /** Default title when page_title is empty */
  defaultTitle?: string
  /** ID prefix for generating unique row IDs */
  idPrefix?: string
}

const defaultOptions: Required<PageViewsColumnOptions> = {
  pageColumnHeader: __('Page', 'wp-statistics'),
  maxTitleLength: 40,
  defaultTitle: __('Unknown', 'wp-statistics'),
  idPrefix: 'page',
}

/**
 * Transform API response to PageViewsData interface
 */
export function transformPageViewsData(
  record: PageViewsRecord,
  options: PageViewsColumnOptions = {}
): PageViewsData {
  const opts = { ...defaultOptions, ...options }

  return {
    id: `${opts.idPrefix}-${record.page_uri}`,
    pageUri: record.page_uri || '/',
    pageTitle: record.page_title || opts.defaultTitle,
    views: Number(record.views) || 0,
  }
}

/**
 * Create a transform function with preset options
 */
export function createPageViewsTransform(options: PageViewsColumnOptions = {}) {
  return (record: PageViewsRecord) => transformPageViewsData(record, options)
}

/**
 * Create column definitions for Page + Views tables
 */
export function createPageViewsColumns(options: PageViewsColumnOptions = {}): ColumnDef<PageViewsData>[] {
  const opts = { ...defaultOptions, ...options }

  return [
    {
      accessorKey: 'page',
      header: opts.pageColumnHeader,
      cell: ({ row }) => (
        <PageCell
          data={{
            title: row.original.pageTitle,
            url: row.original.pageUri,
          }}
          maxLength={opts.maxTitleLength}
          externalUrl={row.original.pageUri}
        />
      ),
      enableSorting: false,
      meta: {
        title: opts.pageColumnHeader,
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
