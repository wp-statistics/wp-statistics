import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(events)/registered-events')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="registered-events"
      title={__('Registered Events', 'wp-statistics')}
      description={__('View all event types being tracked on your site, including built-in, goal-based, and custom events.', 'wp-statistics')}
    />
  )
}
