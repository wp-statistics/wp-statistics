import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(page-insights)/404-pages')({
  component: () => (
    <PhpReportRoute slug="404-pages" fallbackTitle={__('404 Pages', 'wp-statistics')} />
  ),
})
