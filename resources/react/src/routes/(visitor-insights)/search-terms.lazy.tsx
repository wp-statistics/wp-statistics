import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(visitor-insights)/search-terms')({
  component: () => (
    <PhpReportRoute slug="search-terms" fallbackTitle={__('Search Terms', 'wp-statistics')} />
  ),
})
