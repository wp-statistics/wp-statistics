import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/(page-insights)/page-insights-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpOverviewRoute slug="page-insights-overview" fallbackTitle="Pages Overview" />
}
