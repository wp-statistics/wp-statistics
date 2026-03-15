import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(content-analytics)/top-authors')({
  component: () => (
    <PhpReportRoute slug="top-authors" fallbackTitle={__('Top Authors', 'wp-statistics')} />
  ),
})
