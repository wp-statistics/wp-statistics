import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(geographic)/european-countries')({
  component: () => (
    <PhpReportRoute slug="european-countries" fallbackTitle={__('European Countries', 'wp-statistics')} />
  ),
})
