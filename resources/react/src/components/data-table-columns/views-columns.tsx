/**
 * Column definitions for the Views data table.
 * Extracted from views.lazy.tsx to improve maintainability.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import {
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
import { parseEntryPage } from '@/lib/url-utils'
import type { ViewRecord } from '@/services/visitor-insight/get-views'

/**
 * Context identifier for user preferences
 */
export const VIEWS_CONTEXT = 'views'

/**
 * Columns hidden by default
 * entryPage is hidden as it's redundant with the page column
 */
export const VIEWS_DEFAULT_HIDDEN_COLUMNS: string[] = ['location', 'entryPage']

/**
 * Column configuration for API column optimization
 */
export const VIEWS_COLUMN_CONFIG: ColumnConfig = {
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
    page: ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id'],
    referrer: ['referrer_domain', 'referrer_channel'],
    entryPage: ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id'],
    totalViews: ['total_views'],
    location: ['country_code', 'country_name', 'region_name', 'city_name'],
  },
  context: VIEWS_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const VIEWS_DEFAULT_API_COLUMNS = getDefaultApiColumns(VIEWS_COLUMN_CONFIG)

/**
 * View data interface for the table
 */
export interface ViewData {
  lastVisit: string
  visitorInfo: {
    country: { code: string; name: string; region: string; city: string }
    os: { icon: string; name: string }
    browser: { icon: string; name: string; version: string }
    user?: { username: string; id: number; email: string; role: string }
    ipAddress?: string
    hash?: string
  }
  page: {
    title: string
    url: string
    type?: string
    wpId?: number | null
    resourceId?: number | null
  }
  referrer: {
    domain?: string
    fullUrl?: string
    category: string
  }
  entryPage: {
    title: string
    url: string
    hasQueryString: boolean
    queryString?: string
    utmCampaign?: string
    type?: string
    wpId?: number | null
    resourceId?: number | null
  }
  totalViews: number
}

/**
 * Transform API response to ViewData interface
 */
export function transformViewData(record: ViewRecord): ViewData {
  const entryPageData = parseEntryPage(record.entry_page, record.entry_page_title)

  return {
    lastVisit: record.last_visit,
    visitorInfo: {
      country: {
        code: record.country_code?.toLowerCase() || '000',
        name: record.country_name || 'Unknown',
        region: record.region_name || '',
        city: record.city_name || '',
      },
      os: {
        icon: record.os_name?.toLowerCase().replace(/\s+/g, '_') || 'unknown',
        name: record.os_name || 'Unknown',
      },
      browser: {
        icon: record.browser_name?.toLowerCase().replace(/\s+/g, '_') || 'unknown',
        name: record.browser_name || 'Unknown',
        version: record.browser_version || '',
      },
      user:
        record.user_id && record.user_login
          ? {
              username: record.user_login,
              id: record.user_id,
              email: record.user_email || '',
              role: record.user_role || '',
            }
          : undefined,
      ipAddress: record.ip_address || undefined,
      hash: record.visitor_hash || undefined,
    },
    page: {
      title: record.entry_page_title || record.entry_page || 'Unknown',
      url: record.entry_page || '/',
      type: record.entry_page_type || undefined,
      wpId: record.entry_page_wp_id ?? null,
      resourceId: record.entry_page_resource_id ?? null,
    },
    referrer: {
      domain: record.referrer_domain || undefined,
      fullUrl: record.referrer_domain ? `https://${record.referrer_domain}` : undefined,
      category: record.referrer_channel?.toUpperCase() || 'DIRECT TRAFFIC',
    },
    entryPage: {
      title: entryPageData.title,
      url: record.entry_page || '/',
      hasQueryString: entryPageData.hasQueryString,
      queryString: entryPageData.queryString,
      utmCampaign: entryPageData.utmCampaign,
      type: record.entry_page_type || undefined,
      wpId: record.entry_page_wp_id ?? null,
      resourceId: record.entry_page_resource_id ?? null,
    },
    totalViews: record.total_views || 0,
  }
}

/**
 * Create column definitions for the Views table
 * Order: visitorInfo → page → lastVisit → totalViews → referrer → entryPage (hidden)
 */
export function createViewsColumns(config: VisitorInfoConfig): ColumnDef<ViewData>[] {
  return [
    // Primary columns - visible by default
    {
      accessorKey: 'visitorInfo',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.visitorInfo,
      enableSorting: false,
      cell: ({ row }) => {
        const visitorInfo = row.getValue('visitorInfo') as ViewData['visitorInfo']
        return (
          <VisitorInfoCell
            data={{ ...visitorInfo, identifier: visitorInfo.hash || visitorInfo.ipAddress }}
            config={config}
          />
        )
      },
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
        const visitorInfo = row.getValue('visitorInfo') as ViewData['visitorInfo']
        return (
          <LocationCell
            data={{
              countryCode: visitorInfo.country.code,
              countryName: visitorInfo.country.name,
              regionName: visitorInfo.country.region || undefined,
              cityName: visitorInfo.country.city || undefined,
            }}
            pluginUrl={config.pluginUrl}
            linkTo="/country/$countryCode"
            linkParams={{ countryCode: visitorInfo.country.code }}
          />
        )
      },
      meta: {
        title: 'Location',
        priority: 'secondary',
      },
    },
    {
      accessorKey: 'page',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.page,
      enableSorting: false,
      cell: ({ row }) => {
        const page = row.getValue('page') as ViewData['page']
        return (
          <PageCell
            data={{
              title: page.title,
              url: page.url,
              pageType: page.type,
              pageWpId: page.wpId,
              resourceId: page.resourceId,
            }}
            maxLength={35}
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
      accessorKey: 'lastVisit',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.lastVisit,
      cell: ({ row }) => <LastVisitCell date={new Date(row.getValue('lastVisit'))} />,
      meta: {
        title: 'Last Visit',
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'totalViews',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.getValue('totalViews') as number} />,
      meta: {
        title: 'Views',
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'referrer',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.referrer,
      enableSorting: false,
      cell: ({ row }) => {
        const referrer = row.getValue('referrer') as ViewData['referrer']
        return (
          <ReferrerCell
            data={{
              domain: referrer.domain,
              category: referrer.category,
            }}
            maxLength={25}
          />
        )
      },
      meta: {
        title: 'Referrer',
        priority: 'secondary',
      },
    },
    // Hidden by default
    {
      accessorKey: 'entryPage',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.entryPage,
      enableSorting: false,
      cell: ({ row }) => {
        const entryPage = row.getValue('entryPage') as ViewData['entryPage']
        return (
          <EntryPageCell
            data={{
              title: entryPage.title,
              url: entryPage.url,
              hasQueryString: entryPage.hasQueryString,
              queryString: entryPage.queryString,
              utmCampaign: entryPage.utmCampaign,
              pageType: entryPage.type,
              pageWpId: entryPage.wpId,
              resourceId: entryPage.resourceId,
            }}
            maxLength={35}
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
