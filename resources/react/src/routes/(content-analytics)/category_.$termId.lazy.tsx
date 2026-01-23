import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { getSingleCategoryQueryOptions } from '@/services/content-analytics/get-single-category'

import { type MetricConfig, SingleEntityPage } from './-components/single-entity-page'

export const Route = createLazyFileRoute('/(content-analytics)/category_/$termId')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

// Category-specific metrics configuration (5 metrics vs author's 6)
const CATEGORY_METRICS: MetricConfig[] = [
  { key: 'visitors', label: __('Visitors', 'wp-statistics'), format: 'number' },
  { key: 'views', label: __('Views', 'wp-statistics'), format: 'number' },
  { key: 'content_count', label: __('Content Count', 'wp-statistics'), format: 'number' },
  { key: 'bounce_rate', label: __('Bounce Rate', 'wp-statistics'), format: 'percentage' },
  { key: 'avg_time_on_page', label: __('Avg. Time on Page', 'wp-statistics'), format: 'duration' },
]

function RouteComponent() {
  const { termId } = Route.useParams()

  return (
    <SingleEntityPage
      entityId={termId}
      entityType="category"
      getQueryOptions={({ entityId, dateFrom, dateTo, compareDateFrom, compareDateTo, filters }) =>
        getSingleCategoryQueryOptions({
          termId: entityId,
          dateFrom,
          dateTo,
          compareDateFrom,
          compareDateTo,
          filters: filters as [],
        })
      }
      metricsKey="category_metrics"
      infoKey="category_info"
      metrics={CATEGORY_METRICS}
      metricsColumns={3}
    />
  )
}
