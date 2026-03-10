import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(visitor-insights)/top-visitors')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="top-visitors" fallbackTitle={__('Top Visitors', 'wp-statistics')} />
}
