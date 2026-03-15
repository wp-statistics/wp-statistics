import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(devices)/operating-systems')({
  component: () => (
    <PhpReportRoute slug="operating-systems" fallbackTitle={__('Operating Systems', 'wp-statistics')} />
  ),
})
