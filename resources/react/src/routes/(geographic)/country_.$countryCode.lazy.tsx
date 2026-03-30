import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { LockIcon } from 'lucide-react'

import { PhpOverviewRoute } from '@/components/php-overview-route'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { WordPress } from '@/lib/wordpress'

export const Route = createLazyFileRoute('/(geographic)/country_/$countryCode')({
  component: RouteComponent,
})

function LockedState() {
  return (
    <Panel className="p-8 text-center">
      <div className="max-w-md mx-auto space-y-4">
        <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
          <LockIcon className="w-8 h-8 text-primary" strokeWidth={1.5} />
        </div>
        <h2 className="text-lg font-semibold text-neutral-800">
          {__('Single Country Report', 'wp-statistics')}
        </h2>
        <p className="text-sm text-muted-foreground">
          {__(
            'Get detailed analytics for individual countries including traffic trends, top regions, top cities, and engagement metrics.',
            'wp-statistics'
          )}
        </p>
        <p className="text-sm text-muted-foreground">
          {__('This feature requires the Premium addon.', 'wp-statistics')}
        </p>
        <a
          href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=single-country"
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
        >
          {__('Upgrade to Premium', 'wp-statistics')}
        </a>
      </div>
    </Panel>
  )
}

function RouteComponent() {
  const { countryCode } = Route.useParams()
  const reports = WordPress.getInstance().getData<Record<string, { type?: string }>>('reports')

  // Premium: PHP config registered by SingleCountry module
  if (reports?.['single-country']?.type === 'detail') {
    return (
      <PhpOverviewRoute
        slug="single-country"
        fallbackTitle={__('Country Report', 'wp-statistics')}
        routeParams={{ countryCode }}
      />
    )
  }

  // Free: show locked state
  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">
          {__('Single Country Report', 'wp-statistics')}
        </h1>
      </div>
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="single-country" />
        <LockedState />
      </div>
    </div>
  )
}
