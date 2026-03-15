import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(referrals)/referred-visitors')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="referred-visitors" fallbackTitle={__('Referred Visitors', 'wp-statistics')} />
}
