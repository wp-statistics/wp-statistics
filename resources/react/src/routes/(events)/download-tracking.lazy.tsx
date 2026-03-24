import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(events)/download-tracking')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="download-tracking"
      title={__('Downloads', 'wp-statistics')}
      description={__('Track file downloads on your site. See which files your visitors download most.', 'wp-statistics')}
    />
  )
}
