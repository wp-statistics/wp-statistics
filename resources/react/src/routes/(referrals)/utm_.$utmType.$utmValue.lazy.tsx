import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(referrals)/utm_/$utmType/$utmValue')({
  component: RouteComponent,
})

function RouteComponent() {
  const { utmType, utmValue } = Route.useParams()

  return (
    <PhpLockedRoute
      slug="single-utm"
      title={__('UTM Detail Report', 'wp-statistics')}
      description={__('Get detailed analytics for individual UTM parameters including traffic trends, referrers, sources, mediums, and entry pages.', 'wp-statistics')}
      utmCampaign="utm-detail"
      routeParams={{ utmType, utmValue }}
    />
  )
}
