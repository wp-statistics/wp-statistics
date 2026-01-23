import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { getSingleAuthorQueryOptions } from '@/services/content-analytics/get-single-author'

import { type MetricConfig, SingleEntityPage } from './-components/single-entity-page'

export const Route = createLazyFileRoute('/(content-analytics)/author_/$authorId')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

// Author-specific metrics configuration
const AUTHOR_METRICS: MetricConfig[] = [
  { key: 'visitors', label: __('Visitors', 'wp-statistics'), format: 'number' },
  { key: 'views', label: __('Views', 'wp-statistics'), format: 'number' },
  { key: 'published_content', label: __('Published Content', 'wp-statistics'), format: 'number' },
  { key: 'bounce_rate', label: __('Bounce Rate', 'wp-statistics'), format: 'percentage' },
  { key: 'avg_time_on_page', label: __('Avg. Time on Page', 'wp-statistics'), format: 'duration' },
  { key: 'comments', label: __('Comments', 'wp-statistics'), format: 'number' },
]

function RouteComponent() {
  const { authorId } = Route.useParams()

  return (
    <SingleEntityPage
      entityId={authorId}
      entityType="author"
      getQueryOptions={({ entityId, dateFrom, dateTo, compareDateFrom, compareDateTo, filters }) =>
        getSingleAuthorQueryOptions({
          authorId: entityId,
          dateFrom,
          dateTo,
          compareDateFrom,
          compareDateTo,
          filters: filters as [],
        })
      }
      metricsKey="author_metrics"
      infoKey="author_info"
      metrics={AUTHOR_METRICS}
      metricsColumns={3}
    />
  )
}
