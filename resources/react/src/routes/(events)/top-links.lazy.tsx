import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(events)/top-links')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="top-links"
      title={__('Top Links', 'wp-statistics')}
      description={__('Track external link clicks on your site. See which outbound URLs your visitors click most.', 'wp-statistics')}
    />
  )
}
