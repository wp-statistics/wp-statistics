import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/(page-insights)/url_/$resourceId')({
  component: RouteComponent,
})

function RouteComponent() {
  const { resourceId } = Route.useParams()

  return (
    <PhpOverviewRoute
      slug="single-url"
      fallbackTitle={__('URL Report', 'wp-statistics')}
      routeParams={{ resourceId }}
    />
  )
}
