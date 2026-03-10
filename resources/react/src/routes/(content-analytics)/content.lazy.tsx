import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/(content-analytics)/content')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpOverviewRoute slug="content-overview" fallbackTitle={__('Content', 'wp-statistics')} />
}
