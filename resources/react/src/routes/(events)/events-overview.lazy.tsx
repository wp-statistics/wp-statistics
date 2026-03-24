import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/(events)/events-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpOverviewRoute slug="events-overview" fallbackTitle="Events Overview" />
}
