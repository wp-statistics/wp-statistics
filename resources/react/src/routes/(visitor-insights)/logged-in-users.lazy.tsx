import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(visitor-insights)/logged-in-users')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="logged-in-users" fallbackTitle={__('Logged-in Users', 'wp-statistics')} />
}
