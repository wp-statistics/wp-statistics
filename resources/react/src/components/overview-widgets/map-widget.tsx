import { GlobalMap } from '@/components/custom/global-map'
import { WordPress } from '@/lib/wordpress'

import type { WidgetRenderContext } from './types'

const pluginUrl = WordPress.getInstance().getPluginUrl()

export function MapWidget({ widget, ctx }: { widget: PhpOverviewWidget; ctx: WidgetRenderContext }) {
  const mapData = ctx.mapDataByWidgetId[widget.id]
  if (!mapData) return null
  return (
    <GlobalMap
      data={mapData}
      isLoading={ctx.isLoading}
      dateFrom={ctx.apiDateParams.date_from}
      dateTo={ctx.apiDateParams.date_to}
      metric={widget.mapConfig!.metric}
      showZoomControls={true}
      showLegend={true}
      pluginUrl={pluginUrl}
      title={widget.mapConfig!.title}
      enableCityDrilldown={widget.mapConfig!.enableCityDrilldown}
      enableMetricToggle={widget.mapConfig!.enableMetricToggle}
      availableMetrics={widget.mapConfig!.availableMetrics}
    />
  )
}
