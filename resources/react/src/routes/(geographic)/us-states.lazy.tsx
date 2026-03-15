import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(geographic)/us-states')({
  component: () => (
    <PhpReportRoute slug="us-states" fallbackTitle={__('US States', 'wp-statistics')} />
  ),
})
