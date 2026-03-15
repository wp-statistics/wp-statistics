import { createLazyFileRoute } from '@tanstack/react-router'

import { PhpReportRoute } from '@/components/php-report-route'

export const Route = createLazyFileRoute('/(referrals)/social-media')({
  component: RouteComponent,
})

function RouteComponent() {
  return <PhpReportRoute slug="social-media" fallbackTitle="Social Media" />
}
