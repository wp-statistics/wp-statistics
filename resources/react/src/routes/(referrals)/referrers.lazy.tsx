import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(referrals)/referrers')({
  component: () => (
    <PhpReportRoute slug="referrers" fallbackTitle={__('Referrers', 'wp-statistics')} />
  ),
})
