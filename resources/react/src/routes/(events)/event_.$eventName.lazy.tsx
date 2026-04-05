import { useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Loader2, LockIcon } from 'lucide-react'

import { OverviewPageRenderer } from '@/components/overview-page-renderer'
import { PhpOverviewRoute } from '@/components/php-overview-route'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export const Route = createLazyFileRoute('/(events)/event_/$eventName')({
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
          {__('Single Event Report', 'wp-statistics')}
        </h2>
        <p className="text-sm text-muted-foreground">
          {__(
            'Get detailed analytics for individual event types including trends, top target URLs, and visitor breakdowns.',
            'wp-statistics'
          )}
        </p>
        <p className="text-sm text-muted-foreground">
          {__('This feature requires the Premium addon.', 'wp-statistics')}
        </p>
        <a
          href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=single-event"
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

const EVENT_SLUG_MAP: Record<string, string> = {
  click: 'single-event-click',
  file_download: 'single-event-download',
}

function RouteComponent() {
  const { eventName } = Route.useParams()
  const search = Route.useSearch() as Record<string, string>
  const reports = WordPress.getInstance().getData<Record<string, { type?: string }>>('reports')
  const isPremium = !!reports?.['single-event']?.type

  const specificSlug = EVENT_SLUG_MAP[eventName]
  const isBuiltIn = !!(specificSlug && reports?.[specificSlug]?.type === 'detail')
  const isDynamic = !isBuiltIn && isPremium

  // For built-in events (click, file_download), use static config
  if (isBuiltIn) {
    const apiFilters = search.event_target_url
      ? { event_target_url: { is: search.event_target_url } }
      : undefined

    return (
      <PhpOverviewRoute
        slug={specificSlug}
        fallbackTitle={__('Event Report', 'wp-statistics')}
        routeParams={{ eventName }}
        apiFilters={apiFilters}
      />
    )
  }

  // For custom events, fetch dynamic config
  if (isDynamic) {
    return <DynamicEventPage eventName={eventName} search={search} />
  }

  // Free: locked state
  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">
          {__('Single Event Report', 'wp-statistics')}
        </h1>
      </div>
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="single-event" />
        <LockedState />
      </div>
    </div>
  )
}

function DynamicEventPage({ eventName, search }: { eventName: string; search: Record<string, string> }) {
  const { apiDateParams, isInitialized } = useGlobalFilters()

  const { data, isLoading } = useQuery({
    queryKey: ['event-detail-config', eventName, apiDateParams?.date_from, apiDateParams?.date_to],
    queryFn: () =>
      clientRequest.get<{ data: Record<string, unknown> }>('', {
        params: {
          action: 'wp_statistics_event_detail_config',
          nonce: WordPress.getInstance().getNonce(),
          event_name: eventName,
          date_from: apiDateParams?.date_from || '',
          date_to: apiDateParams?.date_to || '',
        },
      }),
    enabled: isInitialized,
  })

  const config = data?.data?.data as PhpDetailDefinition | undefined

  if (isLoading || !config) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <Loader2 className="h-8 w-8 animate-spin text-neutral-400" />
      </div>
    )
  }

  const apiFilters = search.event_target_url
    ? { event_target_url: { is: search.event_target_url } }
    : undefined

  return (
    <OverviewPageRenderer
      config={config}
      routeParams={{ eventName }}
      apiFilters={apiFilters}
    />
  )
}
