import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(devices)/screen-resolutions')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="screen-resolutions"
      title={__('Screen Resolutions', 'wp-statistics')}
      description={__("See what screen resolutions your visitors use. Understand your audience's devices to optimize your site's layout and responsiveness.", 'wp-statistics')}
    />
  )
}
