/**
 * Column definitions for the Online Visitors data table.
 * Extracted from online-visitors.lazy.tsx to improve maintainability.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import {
  createVisitorInfoData,
  DurationCell,
  EntryPageCell,
  LastVisitCell,
  NumericCell,
  PageCell,
  ReferrerCell,
  VisitorInfoCell,
  type VisitorInfoConfig,
} from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import { formatReferrerChannel } from '@/lib/filter-utils'
import { parseEntryPage } from '@/lib/url-utils'
import type { OnlineVisitor as APIOnlineVisitor } from '@/services/visitor-insight/get-online-visitors'

/**
 * Context identifier for user preferences
 */
export const ONLINE_VISITORS_CONTEXT = 'online_visitors'

/**
 * Columns hidden by default
 */
export const ONLINE_VISITORS_DEFAULT_HIDDEN_COLUMNS: string[] = ['entryPage', 'lastVisit']

/**
 * Column configuration for API column optimization
 */
export const ONLINE_VISITORS_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['visitor_id', 'visitor_hash'],
  columnDependencies: {
    visitorInfo: [
      'ip_address',
      'country_code',
      'country_name',
      'region_name',
      'city_name',
      'os_name',
      'browser_name',
      'browser_version',
      'user_id',
      'user_login',
      'user_email',
      'user_role',
    ],
    onlineFor: ['total_sessions'],
    page: ['entry_page', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id'],
    totalViews: ['total_views'],
    entryPage: ['entry_page', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id'],
    referrer: ['referrer_domain', 'referrer_channel'],
    lastVisit: ['last_visit'],
  },
  context: ONLINE_VISITORS_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const ONLINE_VISITORS_DEFAULT_API_COLUMNS = getDefaultApiColumns(ONLINE_VISITORS_COLUMN_CONFIG)

/**
 * Online Visitor data interface for the table
 */
export interface OnlineVisitor {
  id: string
  country: string
  countryCode: string
  region: string
  city: string
  os: string
  osName: string
  browser: string
  browserName: string
  browserVersion: string
  userId?: string
  username?: string
  email?: string
  userRole?: string
  ipAddress?: string
  hash?: string
  onlineFor: number
  page: string
  pageTitle: string
  pageType?: string
  pageWpId?: number | null
  pageResourceId?: number | null
  totalViews: number
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  entryPageType?: string
  entryPageWpId?: number | null
  entryPageResourceId?: number | null
  referrerDomain?: string
  referrerCategory: string
  lastVisit: Date
}

/**
 * Transform API response to OnlineVisitor interface
 */
export function transformOnlineVisitorData(apiVisitor: APIOnlineVisitor): OnlineVisitor {
  const lastVisitDate = new Date(apiVisitor.last_visit)
  const onlineForSeconds = Math.max(0, apiVisitor.total_sessions * 60)
  const entryPageData = parseEntryPage(apiVisitor.entry_page)

  const getPageTitle = (path: string): string => {
    if (!path || path === '/') return 'Home'
    const segments = path.split('/').filter(Boolean)
    const lastSegment = segments[segments.length - 1] || ''
    return lastSegment.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
  }

  return {
    id: `visitor-${apiVisitor.visitor_id}`,
    country: apiVisitor.country_name || 'Unknown',
    countryCode: (apiVisitor.country_code || '000').toLowerCase(),
    region: apiVisitor.region_name || '',
    city: apiVisitor.city_name || '',
    os: (apiVisitor.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
    osName: apiVisitor.os_name || 'Unknown',
    browser: (apiVisitor.browser_name || 'unknown').toLowerCase(),
    browserName: apiVisitor.browser_name || 'Unknown',
    browserVersion: apiVisitor.browser_version || '',
    userId: apiVisitor.user_id ? String(apiVisitor.user_id) : undefined,
    username: apiVisitor.user_login || undefined,
    email: apiVisitor.user_email || undefined,
    userRole: apiVisitor.user_role || undefined,
    ipAddress: apiVisitor.ip_address || undefined,
    hash: apiVisitor.visitor_hash || undefined,
    onlineFor: onlineForSeconds,
    page: entryPageData.path,
    pageTitle: getPageTitle(entryPageData.path),
    pageType: apiVisitor.entry_page_type || undefined,
    pageWpId: apiVisitor.entry_page_wp_id ?? null,
    pageResourceId: apiVisitor.entry_page_resource_id ?? null,
    totalViews: apiVisitor.total_views || 0,
    entryPage: entryPageData.path,
    entryPageTitle: getPageTitle(entryPageData.path),
    entryPageHasQuery: entryPageData.hasQueryString,
    entryPageQueryString: entryPageData.queryString,
    entryPageType: apiVisitor.entry_page_type || undefined,
    entryPageWpId: apiVisitor.entry_page_wp_id ?? null,
    entryPageResourceId: apiVisitor.entry_page_resource_id ?? null,
    referrerDomain: apiVisitor.referrer_domain || undefined,
    referrerCategory: formatReferrerChannel(apiVisitor.referrer_channel),
    lastVisit: lastVisitDate,
  }
}

/**
 * Create column definitions for the Online Visitors table
 */
export function createOnlineVisitorsColumns(config: VisitorInfoConfig): ColumnDef<OnlineVisitor>[] {
  return [
    {
      accessorKey: 'visitorInfo',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Visitor Info" />,
      enableSorting: false,
      cell: ({ row }) => <VisitorInfoCell data={createVisitorInfoData(row.original)} config={config} />,
      meta: {
        title: 'Visitor Info',
        priority: 'primary',
        cardPosition: 'header',
        mobileLabel: 'Visitor',
      },
    },
    {
      accessorKey: 'page',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Page" />,
      enableSorting: false,
      cell: ({ row }) => (
        <PageCell
          data={{
            title: row.original.pageTitle,
            url: row.original.page,
            pageType: row.original.pageType,
            pageWpId: row.original.pageWpId,
            resourceId: row.original.pageResourceId,
          }}
          maxLength={35}
        />
      ),
      meta: {
        priority: 'primary',
        cardPosition: 'header',
        mobileLabel: 'Page',
      },
    },
    {
      accessorKey: 'onlineFor',
      header: ({ column, table }) => (
        <DataTableColumnHeader column={column} table={table} title="Online" className="text-right" />
      ),
      size: COLUMN_SIZES.onlineFor,
      cell: ({ row }) => <DurationCell seconds={row.original.onlineFor} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: 'Online',
      },
    },
    {
      accessorKey: 'totalViews',
      header: ({ column, table }) => (
        <DataTableColumnHeader column={column} table={table} title="Views" className="text-right" />
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.totalViews} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: 'Views',
      },
    },
    {
      accessorKey: 'referrer',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Referrer" />,
      enableSorting: false,
      cell: ({ row }) => (
        <ReferrerCell
          data={{
            domain: row.original.referrerDomain,
            category: row.original.referrerCategory,
          }}
          maxLength={25}
        />
      ),
      meta: {
        priority: 'secondary',
        mobileLabel: 'Referrer',
      },
    },
    // Hidden by default
    {
      accessorKey: 'entryPage',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Entry Page" />,
      enableSorting: false,
      cell: ({ row }) => {
        const visitor = row.original
        return (
          <EntryPageCell
            data={{
              title: visitor.entryPageTitle,
              url: visitor.entryPage,
              hasQueryString: visitor.entryPageHasQuery,
              queryString: visitor.entryPageQueryString,
              pageType: visitor.entryPageType,
              pageWpId: visitor.entryPageWpId,
              resourceId: visitor.entryPageResourceId,
            }}
            maxLength={35}
          />
        )
      },
      meta: {
        priority: 'secondary',
        mobileLabel: 'Entry',
      },
    },
    {
      accessorKey: 'lastVisit',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Last Visit" />,
      cell: ({ row }) => <LastVisitCell date={row.original.lastVisit} />,
      meta: {
        priority: 'secondary',
        mobileLabel: 'Last Visit',
      },
    },
  ]
}
