import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(referrals)/source-categories')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="source-categories" fallbackTitle="Source Categories" />
}
