/**
 * Column definitions for the Logged-in Users data table.
 * Extracted from logged-in-users.lazy.tsx to improve maintainability.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { type Filter, type FilterField,getOperatorDisplay } from '@/components/custom/filter-button'
import {
  createLocationData,
  createVisitorInfoData,
  EntryPageCell,
  LastVisitCell,
  LocationCell,
  NumericCell,
  PageCell,
  ReferrerCell,
  VisitorInfoCell,
  type VisitorInfoConfig,
} from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import { formatReferrerChannel } from '@/lib/filter-utils'
import { getAnalyticsRoute, parseEntryPage } from '@/lib/url-utils'
import type { LoggedInUser as LoggedInUserRecord } from '@/services/visitor-insight/get-logged-in-users'

/**
 * Context identifier for user preferences
 */
export const LOGGED_IN_USERS_CONTEXT = 'logged_in_users'

/**
 * Columns hidden by default
 * entryPage is hidden as it's redundant with the page column
 */
export const LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS: string[] = ['location', 'entryPage']

/**
 * Column configuration for API column optimization
 */
export const LOGGED_IN_USERS_COLUMN_CONFIG: ColumnConfig = {
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
    lastVisit: ['last_visit'],
    page: ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id'],
    referrer: ['referrer_domain', 'referrer_channel'],
    entryPage: ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id'],
    totalViews: ['total_views'],
    location: ['country_code', 'country_name', 'region_name', 'city_name'],
  },
  context: LOGGED_IN_USERS_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const LOGGED_IN_USERS_DEFAULT_API_COLUMNS = getDefaultApiColumns(LOGGED_IN_USERS_COLUMN_CONFIG)

/**
 * Logged-in User data interface for the table
 */
export interface LoggedInUser {
  id: string
  lastVisit: Date
  country: string
  countryCode: string
  region: string
  city: string
  os: string // lowercase with underscores for icon path
  osName: string // original name for tooltip
  browser: string // lowercase for icon path
  browserName: string // original name for tooltip
  browserVersion: string
  userId: string
  username: string
  email: string
  userRole: string
  referrerDomain?: string
  referrerCategory: string
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  entryPageType?: string
  entryPageWpId?: number | null
  page: string
  pageTitle: string
  pageType?: string
  pageWpId?: number | null
  totalViews: number
}

/**
 * Transform API response to LoggedInUser interface
 */
export function transformLoggedInUserData(record: LoggedInUserRecord): LoggedInUser {
  // Parse entry page data using shared utility
  const entryPageData = parseEntryPage(record.entry_page, record.entry_page_title)

  return {
    id: `user-${record.visitor_id}`,
    lastVisit: new Date(record.last_visit),
    country: record.country_name || 'Unknown',
    countryCode: (record.country_code || '000').toLowerCase(),
    region: record.region_name || '',
    city: record.city_name || '',
    os: (record.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
    osName: record.os_name || 'Unknown',
    browser: (record.browser_name || 'unknown').toLowerCase(),
    browserName: record.browser_name || 'Unknown',
    browserVersion: record.browser_version || '',
    userId: String(record.user_id),
    username: record.user_login || 'user',
    email: record.user_email || '',
    userRole: record.user_role || '',
    referrerDomain: record.referrer_domain || undefined,
    referrerCategory: formatReferrerChannel(record.referrer_channel),
    entryPage: entryPageData.path,
    entryPageTitle: entryPageData.title,
    entryPageHasQuery: entryPageData.hasQueryString,
    entryPageQueryString: entryPageData.queryString,
    entryPageType: record.entry_page_type || undefined,
    entryPageWpId: record.entry_page_wp_id ?? null,
    page: record.entry_page || '/',
    pageTitle: record.entry_page_title || record.entry_page || 'Unknown',
    pageType: record.entry_page_type || undefined,
    pageWpId: record.entry_page_wp_id ?? null,
    totalViews: record.total_views || 0,
  }
}

/**
 * Create default filters for logged-in users page
 */
export function getLoggedInUsersDefaultFilters(filterFields: FilterField[]): Filter[] {
  const field = filterFields.find((f) => f.name === 'logged_in')
  const valueLabel = field?.options?.find((o) => String(o.value) === '1')?.label || 'Logged-in'
  return [
    {
      id: 'logged_in-logged_in-filter-default',
      label: field?.label || 'Login Status',
      operator: getOperatorDisplay('is'),
      rawOperator: 'is',
      value: valueLabel,
      rawValue: '1',
    },
  ]
}

/**
 * Create column definitions for the Logged-in Users table
 * Order: visitorInfo → lastVisit → page → totalViews → referrer → entryPage (hidden)
 */
export function createLoggedInUsersColumns(config: VisitorInfoConfig): ColumnDef<LoggedInUser>[] {
  return [
    // Primary columns - visible by default
    {
      accessorKey: 'visitorInfo',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
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
      accessorKey: 'location',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.location,
      enableSorting: false,
      enableHiding: true,
      cell: ({ row }) => <LocationCell data={createLocationData(row.original)} pluginUrl={config.pluginUrl} />,
      meta: {
        title: 'Location',
        priority: 'secondary',
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
    {
      accessorKey: 'page',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      enableSorting: false,
      cell: ({ row }) => {
        const route = getAnalyticsRoute(row.original.pageType, row.original.pageWpId)
        return (
          <PageCell
            data={{ title: row.original.pageTitle, url: row.original.page }}
            maxLength={35}
            internalLinkTo={route?.to}
            internalLinkParams={route?.params}
          />
        )
      },
      meta: {
        title: 'Page',
        priority: 'primary',
        cardPosition: 'body',
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
      accessorKey: 'referrer',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
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
        title: 'Referrer',
        priority: 'secondary',
      },
    },
    // Hidden by default
    {
      accessorKey: 'entryPage',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      enableSorting: false,
      cell: ({ row }) => {
        const user = row.original
        const route = getAnalyticsRoute(user.entryPageType, user.entryPageWpId)
        return (
          <EntryPageCell
            data={{
              title: user.entryPageTitle,
              url: user.entryPage,
              hasQueryString: user.entryPageHasQuery,
              queryString: user.entryPageQueryString,
            }}
            maxLength={35}
            internalLinkTo={route?.to}
            internalLinkParams={route?.params}
          />
        )
      },
      meta: {
        title: 'Entry Page',
        priority: 'secondary',
        mobileLabel: 'Entry',
      },
    },
  ]
}
