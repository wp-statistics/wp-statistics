import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(devices)/device-categories')({
  component: () => (
    <PhpReportRoute slug="device-categories" fallbackTitle={__('Device Categories', 'wp-statistics')} />
  ),
})
