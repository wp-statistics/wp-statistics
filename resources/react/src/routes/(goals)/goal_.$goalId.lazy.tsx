import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(goals)/goal_/$goalId')({
  component: RouteComponent,
})

function RouteComponent() {
  const { goalId } = Route.useParams()

  return (
    <PhpLockedRoute
      slug="goal-detail"
      title={__('Goal Report', 'wp-statistics')}
      description={__('Get detailed analytics for individual goals including trends and visitor breakdowns.', 'wp-statistics')}
      routeParams={{ goalId }}
    />
  )
}
