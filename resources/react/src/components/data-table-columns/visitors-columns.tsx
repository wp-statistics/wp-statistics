/**
 * Column definitions for the Visitors data table.
 * Extracted from visitors.lazy.tsx to improve maintainability.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import {
  createVisitorInfoData,
  DurationCell,
  JourneyCell,
  LastVisitCell,
  NumericCell,
  ReferrerCell,
  StatusCell,
  VisitorInfoCell,
  type VisitorInfoConfig,
} from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import { formatReferrerChannel } from '@/lib/filter-utils'
import { parseEntryPage } from '@/lib/url-utils'
import type { VisitorRecord } from '@/services/visitor-insight/get-visitors'

/**
 * Context identifier for user preferences
 */
export const VISITORS_CONTEXT = 'visitors_data_table'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const VISITORS_DEFAULT_HIDDEN_COLUMNS = ['viewsPerSession', 'bounceRate', 'visitorStatus']

/**
 * Column configuration for API column optimization
 */
export const VISITORS_COLUMN_CONFIG: ColumnConfig = {
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
      'device_type_name',
      'user_id',
      'user_login',
      'user_email',
      'user_role',
    ],
    referrer: ['referrer_domain', 'referrer_channel'],
    journey: ['entry_page', 'entry_page_title', 'exit_page', 'exit_page_title'],
    totalViews: ['total_views'],
    totalSessions: ['total_sessions'],
    sessionDuration: ['avg_session_duration'],
    viewsPerSession: ['pages_per_session'],
    bounceRate: ['bounce_rate'],
    visitorStatus: ['visitor_status', 'first_visit'],
  },
  context: VISITORS_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const VISITORS_DEFAULT_API_COLUMNS = getDefaultApiColumns(VISITORS_COLUMN_CONFIG)

/**
 * Visitor data interface for the table
 */
export interface Visitor {
  id: string
  lastVisit: Date
  firstVisit: Date
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
  utmCampaign?: string
  exitPage: string
  exitPageTitle: string
  totalViews: number
  totalSessions: number
  sessionDuration: number
  viewsPerSession: number
  bounceRate: number
  visitorStatus: 'new' | 'returning'
}

/**
 * Transform API response to Visitor interface
 */
export function transformVisitorData(record: VisitorRecord): Visitor {
  const entryPageData = parseEntryPage(record.entry_page, record.entry_page_title)

  return {
    id: `visitor-${record.visitor_id}`,
    lastVisit: new Date(record.last_visit),
    firstVisit: record.first_visit ? new Date(record.first_visit) : new Date(record.last_visit),
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
    utmCampaign: entryPageData.utmCampaign,
    exitPage: record.exit_page || '/',
    exitPageTitle: record.exit_page_title || record.exit_page || 'Unknown',
    totalViews: Number(record.total_views) || 0,
    totalSessions: Number(record.total_sessions) || 0,
    sessionDuration: Math.round(Number(record.avg_session_duration) || 0),
    viewsPerSession: Number(record.pages_per_session) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    visitorStatus: record.visitor_status || 'returning',
  }
}

/**
 * Create column definitions for the Visitors table
 */
export function createVisitorsColumns(config: VisitorInfoConfig): ColumnDef<Visitor>[] {
  return [
    // Primary columns
    {
      accessorKey: 'visitorInfo',
      header: () => 'Visitor Info',
      size: COLUMN_SIZES.visitorInfo,
      cell: ({ row }) => <VisitorInfoCell data={createVisitorInfoData(row.original)} config={config} />,
      meta: {
        priority: 'primary',
        cardPosition: 'header',
        mobileLabel: 'Visitor',
      },
    },
    {
      accessorKey: 'lastVisit',
      header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Last Visit" />,
      size: COLUMN_SIZES.lastVisit,
      cell: ({ row }) => <LastVisitCell date={row.original.lastVisit} />,
      meta: {
        priority: 'primary',
        cardPosition: 'header',
        mobileLabel: 'Last Visit',
      },
    },
    {
      accessorKey: 'totalViews',
      header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Views" className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.totalViews} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: 'Views',
      },
    },
    {
      accessorKey: 'totalSessions',
      header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Sessions" className="text-right" />,
      size: COLUMN_SIZES.sessions,
      cell: ({ row }) => <NumericCell value={row.original.totalSessions} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: 'Sessions',
      },
    },
    {
      accessorKey: 'sessionDuration',
      header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Duration" className="text-right" />,
      size: COLUMN_SIZES.duration,
      cell: ({ row }) => <DurationCell seconds={row.original.sessionDuration} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: 'Duration',
      },
    },
    // Secondary columns
    {
      accessorKey: 'referrer',
      header: () => 'Referrer',
      size: COLUMN_SIZES.referrer,
      cell: ({ row }) => (
        <ReferrerCell
          data={{
            domain: row.original.referrerDomain,
            category: row.original.referrerCategory,
          }}
        />
      ),
      meta: {
        priority: 'secondary',
        mobileLabel: 'Referrer',
      },
    },
    {
      accessorKey: 'journey',
      header: () => 'Journey',
      size: COLUMN_SIZES.journey,
      cell: ({ row }) => {
        const visitor = row.original
        const isBounce = visitor.entryPage === visitor.exitPage
        return (
          <JourneyCell
            data={{
              entryPage: {
                title: visitor.entryPageTitle,
                url: visitor.entryPage,
                utmCampaign: visitor.utmCampaign,
              },
              exitPage: {
                title: visitor.exitPageTitle,
                url: visitor.exitPage,
              },
              isBounce,
            }}
          />
        )
      },
      meta: {
        priority: 'secondary',
        mobileLabel: 'Journey',
      },
    },
    // Hidden by default columns
    {
      accessorKey: 'viewsPerSession',
      header: ({ column }) => (
        <DataTableColumnHeaderSortable column={column} title="Per Session" className="text-right" />
      ),
      size: COLUMN_SIZES.viewsPerSession,
      enableHiding: true,
      cell: ({ row }) => <NumericCell value={row.original.viewsPerSession} decimals={1} />,
      meta: {
        priority: 'secondary',
        mobileLabel: 'Per Session',
      },
    },
    {
      accessorKey: 'bounceRate',
      header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Bounce" className="text-right" />,
      size: COLUMN_SIZES.bounceRate,
      enableHiding: true,
      cell: ({ row }) => <NumericCell value={row.original.bounceRate} suffix="%" />,
      meta: {
        priority: 'secondary',
        mobileLabel: 'Bounce',
      },
    },
    {
      accessorKey: 'visitorStatus',
      header: () => 'Status',
      size: COLUMN_SIZES.status,
      enableHiding: true,
      cell: ({ row }) => <StatusCell status={row.original.visitorStatus} firstVisit={row.original.firstVisit} />,
      meta: {
        priority: 'secondary',
        mobileLabel: 'Status',
      },
    },
  ]
}
