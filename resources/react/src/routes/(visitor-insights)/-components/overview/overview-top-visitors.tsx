import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import {
  createTopVisitorsColumns,
  type TopVisitor,
  transformTopVisitorData,
} from '@/components/data-table-columns/top-visitors-columns'
import { WordPress } from '@/lib/wordpress'
import type { TopVisitorRow } from '@/services/visitor-insight/get-visitor-overview'

interface OverviewTopVisitorsProps {
  data?: TopVisitorRow[]
  isFetching?: boolean
}

// Hide columns not useful in the compact overview widget
// Keep visible: visitorInfo, totalViews, referrer, entryPage, exitPage
const OVERVIEW_HIDDEN_COLUMNS = [
  'location',
  'lastVisit',
  'totalSessions',
  'sessionDuration',
  'viewsPerSession',
  'bounceRate',
  'visitorStatus',
]

export const OverviewTopVisitors = ({ data, isFetching }: OverviewTopVisitorsProps) => {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const transformedData = useMemo<TopVisitor[]>(() => {
    if (!data || data.length === 0) {
      return []
    }
    return data.map(transformTopVisitorData)
  }, [data])

  const config = useMemo(
    () => ({
      pluginUrl,
      trackLoggedInEnabled: wp.isTrackLoggedInEnabled(),
      hashEnabled: wp.isHashEnabled(),
    }),
    [pluginUrl, wp]
  )

  const columns = useMemo(() => createTopVisitorsColumns(config), [config])

  return (
    <DataTable
      title={__('Top Visitors', 'wp-statistics')}
      columns={columns}
      data={transformedData}
      rowLimit={10}
      showPagination={false}
      hiddenColumns={OVERVIEW_HIDDEN_COLUMNS}
      isFetching={isFetching}
      emptyStateMessage={__('No visitors found for the selected period', 'wp-statistics')}
      stickyHeader={true}
      fullReportLink={{
        to: '/top-visitors',
      }}
    />
  )
}
