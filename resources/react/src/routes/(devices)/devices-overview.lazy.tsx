import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/(devices)/devices-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpOverviewRoute slug="devices-overview" fallbackTitle="Devices Overview" />
}
