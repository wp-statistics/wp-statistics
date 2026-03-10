import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(geographic)/country-regions')({
  component: () => (
    <PhpReportRoute slug="country-regions" fallbackTitle={__('Regions', 'wp-statistics')} />
  ),
})
