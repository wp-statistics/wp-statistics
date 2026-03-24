import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(events)/top-downloads')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="top-downloads"
      title={__('Top Downloads', 'wp-statistics')}
      description={__('Track file downloads on your site. See which files your visitors download most.', 'wp-statistics')}
    />
  )
}
