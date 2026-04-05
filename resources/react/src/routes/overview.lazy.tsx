import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpOverviewRoute slug="overview" fallbackTitle="Overview" />
}
