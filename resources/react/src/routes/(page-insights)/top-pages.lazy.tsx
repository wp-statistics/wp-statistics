import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(page-insights)/top-pages')({
  component: () => (
    <PhpReportRoute slug="top-pages" fallbackTitle={__('Top Pages', 'wp-statistics')} />
  ),
})
