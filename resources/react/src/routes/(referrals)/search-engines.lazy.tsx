import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(referrals)/search-engines')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="search-engines" fallbackTitle="Search Engines" />
}
