/**
 * Generic column definitions for Page + Views data tables.
 * Reusable across Author Pages, Category Pages, and similar reports.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { StaticSortIndicator } from '@/components/custom/static-sort-indicator'
import { NumericCell, PageCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { getAnalyticsRoute } from '@/lib/url-utils'

/**
 * Generic page data interface for simple page + views tables
 */
export interface PageViewsData {
  id: string
  pageUri: string
  pageTitle: string
  views: number
  pageType?: string
  pageWpId?: number | string
  resourceId?: number | string
}

/**
 * Generic API record interface
 */
export interface PageViewsRecord {
  page_uri: string
  page_title: string
  views: number
  page_type?: string
  page_wp_id?: number | string
  resource_id?: number | string
}

/**
 * Options for customizing the page column
 */
export interface PageViewsColumnOptions {
  /** Header text for the page column (e.g., "Author", "Category", "Page") */
  pageColumnHeader?: string
  /** Header text for the views column (e.g., "Views", "Unique Entrances") */
  viewsColumnHeader?: string
  /** Max length for truncating the title */
  maxTitleLength?: number
  /** Default title when page_title is empty */
  defaultTitle?: string
  /** ID prefix for generating unique row IDs */
  idPrefix?: string
  /** When true, always link rows to /url/$resourceId instead of using getAnalyticsRoute */
  useUrlRoute?: boolean
}

const defaultOptions: Required<PageViewsColumnOptions> = {
  pageColumnHeader: __('Page', 'wp-statistics'),
  viewsColumnHeader: __('Views', 'wp-statistics'),
  maxTitleLength: 40,
  defaultTitle: __('Unknown', 'wp-statistics'),
  idPrefix: 'page',
  useUrlRoute: false,
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
    pageType: record.page_type,
    pageWpId: record.page_wp_id,
    resourceId: record.resource_id,
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
      cell: ({ row }) => {
        const route = opts.useUrlRoute && row.original.resourceId
          ? { to: '/url/$resourceId', params: { resourceId: String(row.original.resourceId) } }
          : getAnalyticsRoute(row.original.pageType, row.original.pageWpId, undefined, row.original.resourceId)
        return (
          <PageCell
            data={{
              title: row.original.pageTitle,
              url: row.original.pageUri,
            }}
            maxLength={opts.maxTitleLength}
            externalUrl={row.original.pageUri}
            internalLinkTo={route?.to}
            internalLinkParams={route?.params}
          />
        )
      },
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
          <StaticSortIndicator title={opts.viewsColumnHeader} direction="desc" />
        </div>
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.views} />,
      enableSorting: false,
      meta: {
        title: opts.viewsColumnHeader,
        priority: 'primary',
        cardPosition: 'body',
      },
    },
  ]
}
