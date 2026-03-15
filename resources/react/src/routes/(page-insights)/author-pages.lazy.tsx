import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(page-insights)/author-pages')({
  component: () => (
    <PhpReportRoute slug="author-pages" fallbackTitle={__('Author Pages', 'wp-statistics')} />
  ),
})
