import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(events)/event-log')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="event-log"
      title={__('Event Log', 'wp-statistics')}
      description={__('View all individual events with visitor details, pages, and event data.', 'wp-statistics')}
    />
  )
}
