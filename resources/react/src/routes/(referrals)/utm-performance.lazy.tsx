import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(referrals)/utm-performance')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="utm-performance"
      title={__('UTM Performance', 'wp-statistics')}
      description={__('Track your UTM campaigns, sources, and mediums with detailed performance reports. Monitor visitor engagement across all your marketing channels.', 'wp-statistics')}
    />
  )
}
