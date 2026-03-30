import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpOverviewRoute } from '@/components/php-overview-route'
import { registerWidget } from '@/registration'
import type { TopVisitorRecord } from '@/services/visitor-insight/get-top-visitors'

import { OverviewTopVisitors } from './-components/overview/overview-top-visitors'

// Register the OverviewTopVisitors DataTable widget for this page.
// The PHP config includes a 'registered' pseudo-widget that renders this.
registerWidget('visitors-overview', {
  id: 'top-visitors',
  label: __('Top Visitors', 'wp-statistics'),
  defaultVisible: true,
  queryId: 'top_visitors',
  render: ({ data, isFetching }) => (
    <OverviewTopVisitors data={data as TopVisitorRecord[]} isFetching={isFetching} />
  ),
})

export const Route = createLazyFileRoute('/(visitor-insights)/visitors-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpOverviewRoute slug="visitors-overview" fallbackTitle="Visitors Overview" />
}
