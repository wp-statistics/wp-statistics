import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(content-analytics)/top-categories')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="top-categories" fallbackTitle="Top Categories" />
}
