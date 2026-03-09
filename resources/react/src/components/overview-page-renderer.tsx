/**
 * Overview Page Renderer
 *
 * Generic renderer for PHP-configured overview pages.
 * Handles: OverviewOptionsProvider wrapping, batch query,
 * widget grid rendering, skeleton loading.
 */

import { keepPreviousData, queryOptions, useQuery } from '@tanstack/react-query'
import { useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { GlobalMap, type GlobalMapData } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { Metrics } from '@/components/custom/metrics'
import {
  type OverviewOptionsConfig,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import type { MetricConfig, WidgetConfig } from '@/contexts/page-options-context'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { clientRequest } from '@/lib/client-request'
import { getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

const pluginUrl = WordPress.getInstance().getPluginUrl()

// Tailwind col-span classes by widget size (must be static for Tailwind JIT)
const COL_SPAN: Record<number, string> = {
  4: 'col-span-12 lg:col-span-4',
  6: 'col-span-12 lg:col-span-6',
  8: 'col-span-12 lg:col-span-8',
  12: 'col-span-12',
}

// Icon render functions by type
const ICON_RENDERERS: Record<OverviewIconType, (item: Record<string, unknown>, slugField: string) => React.ReactNode> = {
  browser: (item, field) => {
    const slug = String(item[field] || 'unknown').toLowerCase().replace(/\s+/g, '_')
    return <img src={`${pluginUrl}public/images/browser/${slug}.svg`} alt={String(item[field] || '')} className="h-4 w-4" />
  },
  os: (item, field) => {
    const slug = String(item[field] || 'unknown').toLowerCase().replace(/[\s/]+/g, '_')
    return <img src={`${pluginUrl}public/images/operating-system/${slug}.svg`} alt={String(item[field] || '')} className="h-4 w-4" />
  },
  country: (item) => {
    const code = String(item.country_code || '000').toLowerCase()
    return <img src={`${pluginUrl}public/images/flags/${code}.svg`} alt={String(item.country_name || '')} className="w-4 h-3" />
  },
  device: (item, field) => {
    const slug = String(item[field] || 'desktop').toLowerCase()
    return <img src={`${pluginUrl}public/images/device/${slug}.svg`} alt={String(item[field] || '')} className="h-4 w-4" />
  },
}

// ------- Batch query types -------

interface BatchQueryResult {
  success?: boolean
  items?: Array<Record<string, unknown>>
  totals?: Record<string, unknown>
  data?: {
    rows: Record<string, unknown>[]
    totals?: Record<string, unknown>
  }
}

interface OverviewBatchResponse {
  success: boolean
  items: Record<string, BatchQueryResult>
}

// ------- Query factory -------

function createOverviewQueryOptions(
  config: PhpOverviewDefinition,
  params: {
    dateFrom: string
    dateTo: string
    compareDateFrom?: string
    compareDateTo?: string
    filters: unknown[]
  }
) {
  const hasCompare = !!(params.compareDateFrom && params.compareDateTo)
  const apiFilters =
    params.filters.length > 0
      ? transformFiltersToApi(params.filters as Parameters<typeof transformFiltersToApi>[0])
      : {}
  const hasFilters = Object.keys(apiFilters).length > 0

  // Set compare on queries that don't explicitly specify it
  const queries = config.queries.map((q) => ({
    ...q,
    compare: q.compare !== undefined ? q.compare : hasCompare,
  }))

  return queryOptions({
    // eslint-disable-next-line @tanstack/query/exhaustive-deps -- apiFilters is included conditionally, queries is static config
    queryKey: [
      config.pageId,
      params.dateFrom,
      params.dateTo,
      params.compareDateFrom,
      params.compareDateTo,
      hasCompare,
      hasFilters ? apiFilters : null,
    ],
    queryFn: () =>
      clientRequest.post<OverviewBatchResponse>(
        '',
        {
          date_from: params.dateFrom,
          date_to: params.dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: params.compareDateFrom,
            previous_date_to: params.compareDateTo,
          }),
          ...(hasFilters && { filters: apiFilters }),
          queries,
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}

// ------- Component -------

export function OverviewPageRenderer({ config }: { config: PhpOverviewDefinition }) {
  const widgetConfigs: WidgetConfig[] = useMemo(
    () =>
      config.widgets.map((w) => ({
        id: w.id,
        label: w.label,
        defaultVisible: true,
        defaultSize: (w.defaultSize || 12) as 4 | 6 | 8 | 12,
      })),
    [config.widgets]
  )

  const metricConfigs: MetricConfig[] = useMemo(
    () =>
      config.metrics.map((m) => ({
        id: m.id,
        label: m.label,
        defaultVisible: true,
      })),
    [config.metrics]
  )

  const optionsConfig: OverviewOptionsConfig = useMemo(
    () => ({
      pageId: config.pageId,
      filterGroup: config.filterGroup as FilterGroup,
      widgetConfigs,
      metricConfigs,
      hideFilters: config.hideFilters,
    }),
    [config.pageId, config.filterGroup, config.hideFilters, widgetConfigs, metricConfigs]
  )

  return (
    <OverviewOptionsProvider config={optionsConfig}>
      <OverviewContent config={config} optionsConfig={optionsConfig} />
    </OverviewOptionsProvider>
  )
}

function OverviewContent({
  config,
  optionsConfig,
}: {
  config: PhpOverviewDefinition
  optionsConfig: OverviewOptionsConfig
}) {
  const { filters: appliedFilters, isInitialized, isCompareEnabled, apiDateParams } = useGlobalFilters()
  const { isWidgetVisible, isMetricVisible } = usePageOptions()
  const options = useOverviewOptions(optionsConfig)
  const navigate = useNavigate()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  const {
    data: batchResponse,
    isLoading,
  } = useQuery({
    ...createOverviewQueryOptions(config, {
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse

  // Build metrics from flat query results
  const overviewMetrics = useMemo(() => {
    const items = batchResponse?.data?.items
    if (!items) return []
    return config.metrics
      .filter((m) => isMetricVisible(m.id))
      .map((m) => {
        const value = items[m.queryId]?.items?.[0]?.[m.valueField]
        return { id: m.id, label: m.label, value: value != null ? String(value) : '-' }
      })
  }, [batchResponse, config.metrics, isMetricVisible])

  // Pre-compute map data for map widgets (memoized to avoid re-creating on every render)
  const mapDataByWidgetId = useMemo(() => {
    const items = batchResponse?.data?.items
    if (!items) return {}
    const result: Record<string, GlobalMapData> = {}
    for (const widget of config.widgets) {
      if (widget.type === 'map' && widget.queryId) {
        const rows = items[widget.queryId]?.data?.rows || []
        result[widget.id] = {
          countries: rows
            .filter((item: Record<string, unknown>) => item.country_code && item.country_name)
            .map((item: Record<string, unknown>) => ({
              code: String(item.country_code).toLowerCase(),
              name: String(item.country_name),
              visitors: Number(item.visitors) || 0,
              views: Number(item.views) || 0,
            })),
        }
      }
    }
    return result
  }, [batchResponse, config.widgets])

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={config.title}
        filterGroup={config.filterGroup as FilterGroup}
        optionsTriggerProps={options.triggerProps}
        showFilterButton={config.showFilterButton ?? !config.hideFilters}
      />

      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-3" currentRoute={config.pageId} />

        {showSkeleton ? (
          <OverviewSkeleton config={config} />
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {config.widgets.map((widget) => {
              if (!isWidgetVisible(widget.id)) return null

              if (widget.type === 'metrics') {
                if (overviewMetrics.length === 0) return null
                return (
                  <div key={widget.id} className="col-span-12">
                    <Panel>
                      <Metrics metrics={overviewMetrics} />
                    </Panel>
                  </div>
                )
              }

              if (widget.type === 'map' && widget.mapConfig) {
                const mapData = mapDataByWidgetId[widget.id]
                if (!mapData) return null
                return (
                  <div key={widget.id} className={COL_SPAN[widget.defaultSize] || 'col-span-12'}>
                    <GlobalMap
                      data={mapData}
                      isLoading={isLoading}
                      dateFrom={apiDateParams.date_from}
                      dateTo={apiDateParams.date_to}
                      metric={widget.mapConfig.metric}
                      showZoomControls={true}
                      showLegend={true}
                      pluginUrl={pluginUrl}
                      title={widget.mapConfig.title}
                      enableCityDrilldown={widget.mapConfig.enableCityDrilldown}
                      enableMetricToggle={widget.mapConfig.enableMetricToggle}
                      availableMetrics={widget.mapConfig.availableMetrics}
                    />
                  </div>
                )
              }

              if (widget.type === 'bar-list' && widget.queryId) {
                const queryResult = batchResponse?.data?.items?.[widget.queryId]
                const rows = queryResult?.data?.rows || []
                const totals = queryResult?.data?.totals

                return (
                  <div key={widget.id} className={COL_SPAN[widget.defaultSize] || 'col-span-12 lg:col-span-6'}>
                    <HorizontalBarList
                      title={widget.label}
                      showComparison={isCompareEnabled}
                      columnHeaders={widget.columnHeaders}
                      items={transformToBarList(rows, {
                        label: (item) => String(item[widget.labelField!] || __('Unknown', 'wp-statistics')),
                        value: (item) => Number(item[widget.valueField!]) || 0,
                        previousValue: (item) => {
                          const prev = item.previous as Record<string, unknown> | undefined
                          return prev ? Number(prev[widget.valueField!]) || 0 : 0
                        },
                        total: getTotalValue(totals?.[widget.valueField!]) || 1,
                        icon:
                          widget.iconType && widget.iconSlugField
                            ? (item) =>
                                ICON_RENDERERS[widget.iconType!](
                                  item as Record<string, unknown>,
                                  widget.iconSlugField!
                                )
                            : undefined,
                        isCompareEnabled,
                        comparisonDateLabel,
                        ...(widget.linkTo &&
                          widget.linkParamField && {
                            linkTo: () => widget.linkTo!,
                            linkParams: (item) => {
                              const value = String(item[widget.linkParamField!] || '').toLowerCase()
                              const paramMatch = widget.linkTo!.match(/\$(\w+)/)
                              return paramMatch ? { [paramMatch[1]]: value } : {}
                            },
                          }),
                      })}
                      link={widget.link ? { action: () => navigate({ to: widget.link!.to }) } : undefined}
                    />
                  </div>
                )
              }

              return null
            })}
          </div>
        )}
      </div>
    </div>
  )
}

function OverviewSkeleton({ config }: { config: PhpOverviewDefinition }) {
  const metricsCount = config.metrics.length

  return (
    <div className="grid gap-3 grid-cols-12">
      {config.widgets.map((w) => {
        if (w.type === 'metrics') {
          return (
            <div key={w.id} className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={metricsCount} columns={metricsCount} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'map') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12'}>
              <PanelSkeleton titleWidth="w-40">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'bar-list') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12 lg:col-span-6'}>
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon={!!w.iconType} />
              </PanelSkeleton>
            </div>
          )
        }
        return null
      })}
    </div>
  )
}
