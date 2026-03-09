import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/(referrals)/referrals-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpOverviewRoute slug="referrals-overview" fallbackTitle="Referrals Overview" />
}
