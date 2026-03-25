import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(goals)/goals')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="goals"
      title={__('Goals', 'wp-statistics')}
      description={__('Track conversion goals for page views, events, and click interactions.', 'wp-statistics')}
    />
  )
}
