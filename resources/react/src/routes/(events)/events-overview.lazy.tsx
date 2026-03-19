import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(events)/events-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="events-overview"
      title={__('Events Overview', 'wp-statistics')}
      description={__('View all tracked events grouped by event name. Monitor clicks, downloads, and custom events on your site.', 'wp-statistics')}
    />
  )
}
