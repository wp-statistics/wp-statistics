import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import {
  createTopVisitorsColumns,
  type TopVisitor,
} from '@/components/data-table-columns/top-visitors-columns'
import { formatReferrerChannel } from '@/lib/filter-utils'
import { parseEntryPage } from '@/lib/url-utils'
import { WordPress } from '@/lib/wordpress'
import type { TopVisitorRow } from '@/services/visitor-insight/get-visitor-overview'

interface OverviewTopVisitorsProps {
  data?: TopVisitorRow[]
}

/**
 * Transform overview API data to TopVisitor interface
 * This allows reuse of the shared column definitions
 */
function transformOverviewVisitorData(record: TopVisitorRow): TopVisitor {
  const entryPageData = parseEntryPage(record.entry_page, record.entry_page_title)

  return {
    id: `visitor-${record.visitor_id}`,
    // Use current date as fallback since overview doesn't have last_visit
    lastVisit: new Date(),
    country: record.country_name || 'Unknown',
    countryCode: (record.country_code || '000').toLowerCase(),
    region: record.region_name || '',
    city: record.city_name || '',
    os: (record.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
    osName: record.os_name || 'Unknown',
    browser: (record.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
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
    utmCampaign: entryPageData.utmCampaign,
    exitPage: record.exit_page || '/',
    exitPageTitle: record.exit_page_title || record.exit_page || 'Unknown',
    exitPageType: record.exit_page_type || undefined,
    exitPageWpId: record.exit_page_wp_id ?? null,
    totalViews: Number(record.total_views) || 0,
    // Default values for fields not available in overview query
    totalSessions: 0,
    sessionDuration: 0,
    viewsPerSession: 0,
    bounceRate: 0,
    visitorStatus: 'returning',
    firstVisit: new Date(),
  }
}

// Columns to show in the overview widget
const OVERVIEW_VISIBLE_COLUMNS = ['visitorInfo', 'totalViews', 'referrer', 'entryPage', 'exitPage']

export const OverviewTopVisitors = ({ data }: OverviewTopVisitorsProps) => {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  // Transform API data using shared transform logic
  const transformedData = useMemo<TopVisitor[]>(() => {
    if (!data || data.length === 0) {
      return []
    }
    return data.map(transformOverviewVisitorData)
  }, [data])

  // Config for visitor info display
  const config = useMemo(
    () => ({
      pluginUrl,
      trackLoggedInEnabled: wp.isTrackLoggedInEnabled(),
      hashEnabled: wp.isHashEnabled(),
    }),
    [pluginUrl, wp]
  )

  // Use shared column definitions, filtered to overview columns
  const columns = useMemo(() => {
    const allColumns = createTopVisitorsColumns(config)
    return allColumns.filter((col) => {
      const key = 'accessorKey' in col ? col.accessorKey : undefined
      return key && OVERVIEW_VISIBLE_COLUMNS.includes(key as string)
    })
  }, [config])

  return (
    <DataTable
      title={__('Top Visitors', 'wp-statistics')}
      columns={columns}
      data={transformedData}
      rowLimit={10}
      showPagination={false}
      showColumnManagement={false}
      fullReportLink={{
        to: '/top-visitors',
      }}
    />
  )
}
