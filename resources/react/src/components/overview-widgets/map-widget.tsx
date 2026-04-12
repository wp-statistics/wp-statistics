import { GlobalMap } from '@/components/custom/global-map'
import { WordPress } from '@/lib/wordpress'

import type { WidgetRenderContext } from './types'

const pluginUrl = WordPress.getInstance().getPluginUrl()

export function MapWidget({ widget, ctx, headerRight, footerLeft }: { widget: PhpOverviewWidget; ctx: WidgetRenderContext; headerRight?: React.ReactNode; footerLeft?: React.ReactNode }) {
  const mapData = ctx.mapDataByWidgetId[widget.id]
  if (!mapData) return null

  const widgetDates = ctx.widgetDateRanges[widget.id]
  const dateFrom = widgetDates?.dateFrom ?? ctx.apiDateParams.date_from
  const dateTo = widgetDates?.dateTo ?? ctx.apiDateParams.date_to

  return (
    <GlobalMap
      data={mapData}
      isLoading={ctx.isLoading}
      dateFrom={dateFrom}
      dateTo={dateTo}
      metric={widget.mapConfig!.metric}
      showZoomControls={true}
      showLegend={true}
      pluginUrl={pluginUrl}
      title={widget.mapConfig!.title}
      enableCityDrilldown={widget.mapConfig!.enableCityDrilldown}
      enableMetricToggle={widget.mapConfig!.enableMetricToggle}
      availableMetrics={widget.mapConfig!.availableMetrics}
      headerRight={headerRight}
      footerLeft={footerLeft}
    />
  )
}
