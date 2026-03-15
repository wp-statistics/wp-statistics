import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/(content-analytics)/authors')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpOverviewRoute slug="authors-overview" fallbackTitle={__('Authors', 'wp-statistics')} />
}
