import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(visitor-insights)/visitors')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="visitors" fallbackTitle={__('Visitors', 'wp-statistics')} />
}
