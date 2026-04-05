import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(page-insights)/category-pages')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="category-pages" fallbackTitle="Category Pages" />
}
