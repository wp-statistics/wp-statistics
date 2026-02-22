/**
 * Column definitions for the Top Visitors data table.
 * Extracted from top-visitors.lazy.tsx to improve maintainability.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { type Filter, getOperatorDisplay } from '@/components/custom/filter-button'
import {
  createLocationData,
  createVisitorInfoData,
  DurationCell,
  EntryPageCell,
  LastVisitCell,
  LocationCell,
  NumericCell,
  PageCell,
  ReferrerCell,
  StatusCell,
  VisitorInfoCell,
  type VisitorInfoConfig,
} from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import { formatReferrerChannel } from '@/lib/filter-utils'
import { parseEntryPage } from '@/lib/url-utils'
import { parseDateTimeString } from '@/lib/wp-date'
import type { TopVisitorRecord } from '@/services/visitor-insight/get-top-visitors'

/**
 * Context identifier for user preferences
 */
export const TOP_VISITORS_CONTEXT = 'top_visitors'

/**
 * Columns hidden by default
 */
export const TOP_VISITORS_DEFAULT_HIDDEN_COLUMNS = [
  'location',
  'referrer',
  'entryPage',
  'exitPage',
  'viewsPerSession',
  'bounceRate',
  'visitorStatus',
]

/**
 * Column configuration for API column optimization
 */
export const TOP_VISITORS_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['visitor_id', 'visitor_hash'],
  columnDependencies: {
    lastVisit: ['last_visit'],
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
    referrer: ['referrer_domain', 'referrer_channel'],
    entryPage: ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id'],
    exitPage: ['exit_page', 'exit_page_title', 'exit_page_type', 'exit_page_wp_id', 'exit_page_resource_id'],
    totalViews: ['total_views'],
    totalSessions: ['total_sessions'],
    sessionDuration: ['avg_session_duration'],
    viewsPerSession: ['pages_per_session'],
    bounceRate: ['bounce_rate'],
    visitorStatus: ['visitor_status', 'first_visit'],
    location: ['country_code', 'country_name', 'region_name', 'city_name'],
  },
  context: TOP_VISITORS_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const TOP_VISITORS_DEFAULT_API_COLUMNS = getDefaultApiColumns(TOP_VISITORS_COLUMN_CONFIG)

/**
 * Default filter: Total Views > 5
 */
export const TOP_VISITORS_DEFAULT_FILTERS: Filter[] = [
  {
    id: 'total_views-total_views-filter-default',
    label: 'Total Views',
    operator: getOperatorDisplay('gt'),
    rawOperator: 'gt',
    value: '5',
    rawValue: '5',
  },
]

/**
 * Top Visitor data interface for the table
 */
export interface TopVisitor {
  id: string
  lastVisit: Date
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
  referrerDomain?: string
  referrerCategory: string
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  entryPageType?: string
  entryPageWpId?: number | null
  entryPageResourceId?: number | null
  utmCampaign?: string
  exitPage: string
  exitPageTitle: string
  exitPageType?: string
  exitPageWpId?: number | null
  exitPageResourceId?: number | null
  totalViews: number
  totalSessions: number
  sessionDuration: number
  viewsPerSession: number
  bounceRate: number
  visitorStatus: 'new' | 'returning'
  firstVisit: Date
}

/**
 * Transform API response to TopVisitor interface
 */
export function transformTopVisitorData(record: TopVisitorRecord): TopVisitor {
  const entryPageData = parseEntryPage(record.entry_page, record.entry_page_title)

  return {
    id: `visitor-${record.visitor_id}`,
    lastVisit: record.last_visit ? parseDateTimeString(record.last_visit) : new Date(),
    country: record.country_name || 'Unknown',
    countryCode: (record.country_code || '000').toLowerCase(),
    region: record.region_name || '',
    city: record.city_name || '',
    os: (record.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
    osName: record.os_name || 'Unknown',
    browser: (record.browser_name || 'unknown').toLowerCase(),
    browserName: record.browser_name || 'Unknown',
    browserVersion: record.browser_version || '',
    userId: record.user_id ? String(record.user_id) : undefined,
    username: record.user_login || undefined,
    email: record.user_email || undefined,
    userRole: record.user_role || undefined,
    ipAddress: record.ip_address || undefined,
    hash: record.visitor_hash || undefined,
    referrerDomain: record.referrer_domain || undefined,
    referrerCategory: formatReferrerChannel(record.referrer_channel),
    entryPage: entryPageData.path,
    entryPageTitle: entryPageData.title,
    entryPageHasQuery: entryPageData.hasQueryString,
    entryPageQueryString: entryPageData.queryString,
    entryPageType: record.entry_page_type || undefined,
    entryPageWpId: record.entry_page_wp_id ?? null,
    entryPageResourceId: record.entry_page_resource_id ?? null,
    utmCampaign: entryPageData.utmCampaign,
    exitPage: record.exit_page || '/',
    exitPageTitle: record.exit_page_title || record.exit_page || 'Unknown',
    exitPageType: record.exit_page_type || undefined,
    exitPageWpId: record.exit_page_wp_id ?? null,
    exitPageResourceId: record.exit_page_resource_id ?? null,
    totalViews: Number(record.total_views) || 0,
    totalSessions: Number(record.total_sessions) || 0,
    sessionDuration: Math.round(Number(record.avg_session_duration) || 0),
    viewsPerSession: Number(record.pages_per_session) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    visitorStatus: record.visitor_status || 'returning',
    firstVisit: record.first_visit
      ? parseDateTimeString(record.first_visit)
      : record.last_visit
        ? parseDateTimeString(record.last_visit)
        : new Date(),
  }
}

/**
 * Create column definitions for the Top Visitors table
 * Order: visitorInfo → totalViews → totalSessions → sessionDuration → lastVisit → (hidden columns)
 */
export function createTopVisitorsColumns(config: VisitorInfoConfig): ColumnDef<TopVisitor>[] {
  return [
    // Primary columns - visible by default
    {
      accessorKey: 'visitorInfo',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      cell: ({ row }) => <VisitorInfoCell data={createVisitorInfoData(row.original)} config={config} />,
      enableSorting: false,
      meta: {
        title: 'Visitor Info',
        priority: 'primary',
        cardPosition: 'header',
        mobileLabel: 'Visitor',
      },
    },
    {
      accessorKey: 'location',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.location,
      enableSorting: false,
      enableHiding: true,
      cell: ({ row }) => {
        const data = createLocationData(row.original)
        return (
          <LocationCell
            data={data}
            pluginUrl={config.pluginUrl}
            linkTo="/country/$countryCode"
            linkParams={{ countryCode: data.countryCode }}
          />
        )
      },
      meta: {
        title: 'Location',
        priority: 'secondary',
      },
    },
    {
      accessorKey: 'totalViews',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.totalViews} />,
      meta: {
        title: 'Views',
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'totalSessions',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.sessions,
      cell: ({ row }) => <NumericCell value={row.original.totalSessions} />,
      meta: {
        title: 'Sessions',
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'sessionDuration',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.duration,
      cell: ({ row }) => <DurationCell seconds={row.original.sessionDuration} />,
      meta: {
        title: 'Duration',
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'lastVisit',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      cell: ({ row }) => <LastVisitCell date={row.original.lastVisit} />,
      meta: {
        title: 'Last Visit',
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    // Hidden by default columns
    {
      accessorKey: 'referrer',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      cell: ({ row }) => (
        <ReferrerCell
          data={{
            domain: row.original.referrerDomain,
            category: row.original.referrerCategory,
          }}
        />
      ),
      enableSorting: false,
      meta: {
        title: 'Referrer',
        priority: 'secondary',
      },
    },
    {
      accessorKey: 'entryPage',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      cell: ({ row }) => {
        const visitor = row.original
        return (
          <EntryPageCell
            data={{
              title: visitor.entryPageTitle,
              url: visitor.entryPage,
              hasQueryString: visitor.entryPageHasQuery,
              queryString: visitor.entryPageQueryString,
              utmCampaign: visitor.utmCampaign,
              pageType: visitor.entryPageType,
              pageWpId: visitor.entryPageWpId,
              resourceId: visitor.entryPageResourceId,
            }}
          />
        )
      },
      enableSorting: false,
      meta: {
        title: 'Entry Page',
        priority: 'secondary',
        mobileLabel: 'Entry',
      },
    },
    {
      accessorKey: 'exitPage',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      cell: ({ row }) => (
        <PageCell
          data={{
            title: row.original.exitPageTitle,
            url: row.original.exitPage,
            pageType: row.original.exitPageType,
            pageWpId: row.original.exitPageWpId,
            resourceId: row.original.exitPageResourceId,
          }}
        />
      ),
      enableSorting: false,
      meta: {
        title: 'Exit Page',
        priority: 'secondary',
        mobileLabel: 'Exit',
      },
    },
    {
      accessorKey: 'viewsPerSession',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.viewsPerSession,
      enableHiding: true,
      cell: ({ row }) => <NumericCell value={row.original.viewsPerSession} decimals={1} />,
      meta: {
        title: 'Per Session',
        priority: 'secondary',
      },
    },
    {
      accessorKey: 'bounceRate',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.bounceRate,
      enableHiding: true,
      cell: ({ row }) => <NumericCell value={row.original.bounceRate} suffix="%" />,
      meta: {
        title: 'Bounce',
        priority: 'secondary',
      },
    },
    {
      accessorKey: 'visitorStatus',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      enableHiding: true,
      enableSorting: false,
      cell: ({ row }) => <StatusCell status={row.original.visitorStatus} firstVisit={row.original.firstVisit} />,
      meta: {
        title: 'Status',
        priority: 'secondary',
      },
    },
  ]
}
