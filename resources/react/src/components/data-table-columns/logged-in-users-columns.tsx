/**
 * Column definitions for the Logged-in Users data table.
 * Extracted from logged-in-users.lazy.tsx to improve maintainability.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { type Filter, getOperatorDisplay, type FilterField } from '@/components/custom/filter-button'
import {
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
import type { LoggedInUser as LoggedInUserRecord } from '@/services/visitor-insight/get-logged-in-users'

/**
 * Context identifier for user preferences
 */
export const LOGGED_IN_USERS_CONTEXT = 'logged_in_users_data_table'

/**
 * Columns hidden by default (none for logged-in users)
 */
export const LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS: string[] = []

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
    page: ['entry_page', 'entry_page_title'],
    referrer: ['referrer_domain', 'referrer_channel'],
    entryPage: ['entry_page', 'entry_page_title'],
    totalViews: ['total_views'],
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
  page: string
  pageTitle: string
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
    page: record.entry_page || '/',
    pageTitle: record.entry_page_title || record.entry_page || 'Unknown',
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
 */
export function createLoggedInUsersColumns(config: VisitorInfoConfig): ColumnDef<LoggedInUser>[] {
  return [
    {
      accessorKey: 'visitorInfo',
      header: 'Visitor Info',
      cell: ({ row }) => {
        const user = row.original
        return (
          <VisitorInfoCell
            data={{
              country: {
                code: user.countryCode,
                name: user.country,
                region: user.region,
                city: user.city,
              },
              os: { icon: user.os, name: user.osName },
              browser: { icon: user.browser, name: user.browserName, version: user.browserVersion },
              user: {
                id: Number(user.userId),
                username: user.username,
                email: user.email,
                role: user.userRole,
              },
            }}
            config={config}
          />
        )
      },
    },
    {
      accessorKey: 'lastVisit',
      header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Last Visit" />,
      cell: ({ row }) => <LastVisitCell date={row.original.lastVisit} />,
    },
    {
      accessorKey: 'page',
      header: 'Page',
      cell: ({ row }) => (
        <PageCell
          data={{ title: row.original.pageTitle, url: row.original.page }}
          maxLength={35}
        />
      ),
    },
    {
      accessorKey: 'referrer',
      header: 'Referrer',
      cell: ({ row }) => (
        <ReferrerCell
          data={{
            domain: row.original.referrerDomain,
            category: row.original.referrerCategory,
          }}
          maxLength={25}
        />
      ),
    },
    {
      accessorKey: 'entryPage',
      header: () => 'Entry Page',
      cell: ({ row }) => {
        const user = row.original
        return (
          <EntryPageCell
            data={{
              title: user.entryPageTitle,
              url: user.entryPage,
              hasQueryString: user.entryPageHasQuery,
              queryString: user.entryPageQueryString,
            }}
            maxLength={35}
          />
        )
      },
    },
    {
      accessorKey: 'totalViews',
      header: ({ column }) => (
        <DataTableColumnHeaderSortable column={column} title="Views" className="text-right" />
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.totalViews} />,
    },
  ]
}
