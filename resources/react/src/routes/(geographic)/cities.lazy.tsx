import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(geographic)/cities')({
  component: () => (
    <PhpReportRoute slug="cities" fallbackTitle={__('Cities', 'wp-statistics')} />
  ),
})
