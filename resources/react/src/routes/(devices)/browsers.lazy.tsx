import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(devices)/browsers')({
  component: () => (
    <PhpReportRoute slug="browsers" fallbackTitle={__('Browsers', 'wp-statistics')} />
  ),
})
