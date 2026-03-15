import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { ExternalLink } from 'lucide-react'

import { PhpReportRoute } from '@/components/php-report-route'
import { usePremiumFeature } from '@/hooks/use-premium-feature'
import { WordPress } from '@/lib/wordpress'

export const Route = createLazyFileRoute('/(visitor-insights)/online-visitors')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpReportRoute
      slug="online-visitors"
      fallbackTitle={__('Online Visitors', 'wp-statistics')}
      headerActions={LiveDashboardLink}
    />
  )
}

function LiveDashboardLink() {
  const { isEnabled } = usePremiumFeature('realtime-stats')
  if (!isEnabled) return null
  const siteUrl = WordPress.getInstance().getSiteUrl()
  return (
    <a
      href={siteUrl + '/wps-live/'}
      target="_blank"
      rel="noopener noreferrer"
      className="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-100"
    >
      <span className="relative flex h-1.5 w-1.5">
        <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75 [animation-duration:2s]" />
        <span className="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500" />
      </span>
      {__('Live Dashboard', 'wp-statistics')}
      <ExternalLink className="h-3 w-3" />
    </a>
  )
}
