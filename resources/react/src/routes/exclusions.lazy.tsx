import { __ } from '@wordpress/i18n'
import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/exclusions')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="exclusions" fallbackTitle={__('Exclusions', 'wp-statistics')} />
}
