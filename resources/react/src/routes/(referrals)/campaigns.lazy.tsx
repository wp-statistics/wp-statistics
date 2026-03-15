import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Megaphone } from 'lucide-react'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(referrals)/campaigns')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="campaigns"
      title={__('Campaigns', 'wp-statistics')}
      description={__('Track your marketing campaigns with detailed UTM reports. Monitor campaign performance, measure ROI, and optimize your marketing strategy.', 'wp-statistics')}
      icon={Megaphone}
      buttonText={__('Learn More', 'wp-statistics')}
      premiumText={__('This feature requires WP Statistics Premium.', 'wp-statistics')}
    />
  )
}
