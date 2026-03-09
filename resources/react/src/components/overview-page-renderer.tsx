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
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { BackButton } from '@/components/custom/back-button'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { GlobalMap, type GlobalMapData } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import {
  OptionsDrawerTrigger,
  type OverviewOptionsConfig,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import { getChannelDisplayName } from '@/components/data-table-columns/source-categories-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useContentRegistry } from '@/contexts/content-registry-context'
import type { MetricConfig, WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { clientRequest } from '@/lib/client-request'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { calcSharePercentage, decodeText, formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
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

// ------- Label transforms -------

const LABEL_TRANSFORMS: Record<BarListLabelTransform, (value: string) => string> = {
  'source-category': getChannelDisplayName,
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

// ------- Timeframe helpers -------

type Timeframe = 'daily' | 'weekly' | 'monthly'

const TIMEFRAME_TO_GROUP_BY: Record<Timeframe, string> = {
  daily: 'date',
  weekly: 'week',
  monthly: 'month',
}

/** Config type accepted by the renderer: overview or detail */
type PageConfig = PhpOverviewDefinition | PhpDetailDefinition

/** Check if any chart widget in config has timeframeSupport */
function hasTimeframeSupport(config: PageConfig): boolean {
  return config.widgets.some((w) => w.type === 'chart' && w.chartConfig?.timeframeSupport)
}

// ------- Query factory -------

function createOverviewQueryOptions(
  config: PageConfig,
  params: {
    dateFrom: string
    dateTo: string
    compareDateFrom?: string
    compareDateTo?: string
    filters: unknown[]
    timeframe?: Timeframe
    /** Entity filter for detail pages (e.g., { key: 'country', operator: 'is', value: 'US' }) */
    entityFilter?: { key: string; operator: string; value: string }
  }
) {
  const hasCompare = !!(params.compareDateFrom && params.compareDateTo)
  const apiFilters =
    params.filters.length > 0
      ? transformFiltersToApi(params.filters as Parameters<typeof transformFiltersToApi>[0])
      : {}
  const hasFilters = Object.keys(apiFilters).length > 0
  const dateGroupBy = TIMEFRAME_TO_GROUP_BY[params.timeframe || 'daily']

  // Set compare on queries that don't explicitly specify it
  // Replace group_by for queries with timeframeGroupBy flag
  // Inject entity filter into each query for detail pages
  const queries = config.queries.map((q) => ({
    ...q,
    compare: q.compare !== undefined ? q.compare : hasCompare,
    ...(q.timeframeGroupBy && { group_by: [dateGroupBy] }),
    ...(params.entityFilter && {
      filters: [...(q.filters as unknown[] || []), params.entityFilter],
    }),
  }))

  return queryOptions({
    // eslint-disable-next-line @tanstack/query/exhaustive-deps -- apiFilters is included conditionally, queries is static config
    queryKey: [
      config.pageId,
      params.entityFilter?.value || null,
      params.dateFrom,
      params.dateTo,
      params.compareDateFrom,
      params.compareDateTo,
      hasFilters ? apiFilters : null,
      params.timeframe || null,
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

export function OverviewPageRenderer({
  config,
  routeParams,
}: {
  config: PageConfig
  routeParams?: Record<string, string>
}) {
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
      <OverviewContent config={config} optionsConfig={optionsConfig} routeParams={routeParams} />
    </OverviewOptionsProvider>
  )
}

function OverviewContent({
  config,
  optionsConfig,
  routeParams,
}: {
  config: PageConfig
  optionsConfig: OverviewOptionsConfig
  routeParams?: Record<string, string>
}) {
  const { dateFrom, dateTo, compareDateFrom, compareDateTo, period, filters: appliedFilters, isInitialized, isCompareEnabled, apiDateParams, handleDateRangeUpdate } = useGlobalFilters()
  const { isWidgetVisible, isMetricVisible } = usePageOptions()
  const options = useOverviewOptions(optionsConfig)
  const navigate = useNavigate()
  const { label: comparisonDateLabel } = useComparisonDateLabel()
  const calcPercentage = usePercentageCalc()

  // Get JS-registered widgets for this page (from premium modules or core)
  const { getWidgetsForPage } = useContentRegistry()
  const registeredWidgets = getWidgetsForPage(config.pageId)

  // Timeframe state (only used when a chart widget has timeframeSupport)
  const supportsTimeframe = hasTimeframeSupport(config)
  const [timeframe, setTimeframe] = useState<Timeframe>('daily')
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevFiltersRef = useRef<string>(JSON.stringify(appliedFilters))
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  useEffect(() => {
    if (!supportsTimeframe) return
    const currentFilters = JSON.stringify(appliedFilters)
    if (currentFilters !== prevFiltersRef.current || dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current) {
      setIsTimeframeOnlyChange(false)
    }
    prevFiltersRef.current = currentFilters
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [supportsTimeframe, appliedFilters, dateFrom, dateTo])

  const handleTimeframeChange = useCallback((newTimeframe: Timeframe) => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  // Build entity filter for detail pages
  const detailConfig = config.type === 'detail' ? config : undefined
  const entityValue = detailConfig?.entityParam ? routeParams?.[detailConfig.entityParam] : undefined
  const entityFilter = detailConfig && entityValue
    ? { key: detailConfig.filterField, operator: 'is', value: entityValue }
    : undefined

  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...createOverviewQueryOptions(config, {
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
      timeframe: supportsTimeframe ? timeframe : undefined,
      entityFilter,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse
  const isChartRefetching = supportsTimeframe && isFetching && !isLoading && isTimeframeOnlyChange

  // Build metrics from query results (supports items-based, totals-based, and computed)
  const overviewMetrics = useMemo(() => {
    const items = batchResponse?.data?.items
    if (!items) return []
    return config.metrics
      .filter((m) => isMetricVisible(m.id))
      .map((m) => {
        const queryResult = items[m.queryId]
        let rawValue: unknown
        let rawPrevious: unknown

        if (m.source === 'computed' && m.computed) {
          // Computed: ratio of numerator/denominator from different queries
          const numQuery = items[m.computed.numeratorQueryId]
          const denQuery = items[m.computed.denominatorQueryId]
          const numTotals = numQuery?.totals?.[m.computed.numeratorField]
          const denTotals = denQuery?.totals?.[m.computed.denominatorField]
          const numCurrent = getTotalValue(numTotals)
          const denCurrent = getTotalValue(denTotals)
          const numPrev = getTotalValue((numTotals as Record<string, unknown>)?.previous)
          const denPrev = getTotalValue((denTotals as Record<string, unknown>)?.previous)
          if (m.computed.type === 'ratio') {
            rawValue = denCurrent > 0 ? numCurrent / denCurrent : 0
            rawPrevious = denPrev > 0 ? numPrev / denPrev : 0
          } else {
            rawValue = calcSharePercentage(numCurrent, denCurrent)
            rawPrevious = calcSharePercentage(numPrev, denPrev)
          }
        } else if (m.source === 'totals') {
          // Read from totals (supports {current, previous} structure)
          const totalsValue = queryResult?.totals?.[m.valueField]
          rawValue = getTotalValue(totalsValue)
          rawPrevious = getTotalValue((totalsValue as Record<string, unknown>)?.previous)
        } else {
          // Default: read from items[0] (flat format) or data.rows[0] (table format)
          const firstRow = queryResult?.items?.[0] || queryResult?.data?.rows?.[0]
          rawValue = firstRow?.[m.valueField]
          if (m.decode && typeof rawValue === 'string') {
            rawValue = decodeText(rawValue)
          }
        }

        // Format the value based on format type
        const formatValue = (val: unknown): string => {
          if (val == null) return '-'
          const num = Number(val)
          switch (m.format) {
            case 'compact_number': return formatCompactNumber(num)
            case 'duration': return formatDuration(num)
            case 'decimal': return formatDecimal(num)
            case 'percentage': return `${formatDecimal(num)}%`
            default: return String(val)
          }
        }

        const formatted = formatValue(rawValue)

        // Build comparison props for totals-based/computed numeric metrics
        const hasComparison = (m.source === 'totals' || m.source === 'computed') && isCompareEnabled && rawPrevious != null
        if (hasComparison) {
          const current = Number(rawValue) || 0
          const previous = Number(rawPrevious) || 0
          return {
            id: m.id,
            label: m.label,
            value: formatted,
            ...calcPercentage(current, previous),
            comparisonDateLabel,
            previousValue: formatValue(previous),
          }
        }

        return { id: m.id, label: m.label, value: formatted }
      })
  }, [batchResponse, config.metrics, isMetricVisible, isCompareEnabled, calcPercentage, comparisonDateLabel])

  // Pre-compute map data for map widgets
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

  // Extract entity display name from response (for detail pages)
  const entityTitle = useMemo(() => {
    if (!detailConfig?.entityInfo) return config.title
    const infoResult = batchResponse?.data?.items?.[detailConfig.entityInfo.queryId]
    const rows = infoResult?.data?.rows || infoResult?.items
    const firstRow = Array.isArray(rows) ? rows[0] : undefined
    return firstRow?.[detailConfig.entityInfo.nameField] as string || config.title
  }, [batchResponse, detailConfig, config.title])

  return (
    <div className="min-w-0">
      {detailConfig ? (
        <div className="flex items-center justify-between px-4 py-3">
          <div className="flex items-center gap-3">
            <BackButton defaultTo={detailConfig.backLink || '/'} label={detailConfig.backLabel} />
            <h1 className="text-2xl font-semibold text-neutral-800 truncate max-w-[400px]" title={entityTitle}>
              {showSkeleton ? __('Loading...', 'wp-statistics') : entityTitle}
            </h1>
          </div>
          <div className="flex items-center gap-3" data-pdf-hide>
            <DateRangePicker
              initialDateFrom={dateFrom}
              initialDateTo={dateTo}
              initialCompareFrom={compareDateFrom}
              initialCompareTo={compareDateTo}
              initialPeriod={period}
              showCompare={true}
              onUpdate={handleDateRangeUpdate}
              align="end"
            />
            <OptionsDrawerTrigger {...options.triggerProps} />
          </div>
        </div>
      ) : (
        <ReportPageHeader
          title={config.title}
          filterGroup={config.filterGroup as FilterGroup}
          optionsTriggerProps={options.triggerProps}
          showFilterButton={config.showFilterButton ?? !config.hideFilters}
        />
      )}

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

              if (widget.type === 'chart' && widget.queryId && widget.chartConfig) {
                return (
                  <div key={widget.id} className={COL_SPAN[widget.defaultSize] || 'col-span-12'}>
                    <ChartWidget
                      widget={widget}
                      queryResult={batchResponse?.data?.items?.[widget.queryId]}
                      isCompareEnabled={isCompareEnabled}
                      timeframe={timeframe}
                      onTimeframeChange={widget.chartConfig.timeframeSupport ? handleTimeframeChange : undefined}
                      loading={isChartRefetching}
                      apiDateParams={apiDateParams}
                    />
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

                // Build link resolvers based on linkType or linkTo+linkParamField
                let linkResolvers: Record<string, unknown> = {}
                if (widget.linkType === 'analytics-route') {
                  linkResolvers = {
                    linkTo: (item: Record<string, unknown>) => {
                      const route = getAnalyticsRoute(item.page_type as string, item.page_wp_id as number, undefined, item.resource_id as number)
                      return route?.to
                    },
                    linkParams: (item: Record<string, unknown>) => {
                      const route = getAnalyticsRoute(item.page_type as string, item.page_wp_id as number, undefined, item.resource_id as number)
                      return route?.params
                    },
                  }
                } else if (widget.linkTo && widget.linkParamField) {
                  const paramMatch = widget.linkTo.match(/\$(\w+)/)
                  const paramName = paramMatch?.[1]
                  linkResolvers = {
                    linkTo: (item: Record<string, unknown>) => item[widget.linkParamField!] ? widget.linkTo! : undefined,
                    linkParams: (item: Record<string, unknown>) => {
                      const value = String(item[widget.linkParamField!] || '').toLowerCase()
                      return paramName ? { [paramName]: value } : {}
                    },
                  }
                }

                return (
                  <div key={widget.id} className={COL_SPAN[widget.defaultSize] || 'col-span-12 lg:col-span-6'}>
                    <HorizontalBarList
                      title={widget.label}
                      showComparison={isCompareEnabled}
                      columnHeaders={widget.columnHeaders}
                      items={transformToBarList(rows, {
                        label: (item) => {
                          // Resolve label: try labelField, then fallback fields
                          let raw = widget.labelField ? item[widget.labelField] : undefined
                          if (!raw && widget.labelFallbackFields) {
                            for (const field of widget.labelFallbackFields) {
                              raw = item[field]
                              if (raw) break
                            }
                          }
                          const label = String(raw || __('Unknown', 'wp-statistics'))
                          // Apply named transform if specified
                          return widget.labelTransform
                            ? LABEL_TRANSFORMS[widget.labelTransform](label)
                            : label
                        },
                        value: (item) => Number(item[widget.valueField!]) || 0,
                        previousValue: (item) => {
                          const prev = item.previous as Record<string, unknown> | undefined
                          return prev ? Number(prev[widget.valueField!]) || 0 : 0
                        },
                        total: getTotalValue(totals?.[widget.valueField!])
                          || rows.reduce((sum: number, row: Record<string, unknown>) => sum + (Number(row[widget.valueField!]) || 0), 0)
                          || 1,
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
                        ...linkResolvers,
                      })}
                      link={widget.link ? { action: () => navigate({ to: widget.link!.to }) } : undefined}
                    />
                  </div>
                )
              }

              // Registered widgets: insert JS-registered widgets at this position
              if (widget.type === 'registered') {
                return registeredWidgets.map((rw) => {
                  if (!isWidgetVisible(rw.id)) return null
                  const widgetData = batchResponse?.data?.items?.[rw.queryId]
                  const data = (widgetData as { data?: { rows?: unknown[] } })?.data?.rows || []
                  const totals = (widgetData as { data?: { totals?: Record<string, unknown> } })?.data?.totals || {}
                  return (
                    <div key={rw.id} className={COL_SPAN[widget.defaultSize] || 'col-span-12'}>
                      {rw.render({
                        data,
                        totals,
                        isCompareEnabled,
                        comparisonDateLabel,
                        isFetching,
                        navigate,
                        getTotalFromResponse: (t, key) => getTotalValue(t?.[key]),
                      })}
                    </div>
                  )
                })
              }

              return null
            })}
          </div>
        )}
      </div>
    </div>
  )
}

// ------- Chart Widget -------

function ChartWidget({
  widget,
  queryResult,
  isCompareEnabled,
  timeframe,
  onTimeframeChange,
  loading,
  apiDateParams,
}: {
  widget: PhpOverviewWidget
  queryResult: BatchQueryResult | undefined
  isCompareEnabled: boolean
  timeframe: Timeframe
  onTimeframeChange?: (tf: Timeframe) => void
  loading: boolean
  apiDateParams: { date_to: string; previous_date_to?: string }
}) {
  const { data: chartData, metrics } = useChartData(queryResult, {
    metrics: widget.chartConfig!.metrics.map((m) => ({
      key: m.key,
      label: m.label,
      color: m.color,
    })),
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  return (
    <LineChart
      title={widget.label}
      data={chartData}
      metrics={metrics}
      showPreviousPeriod={isCompareEnabled}
      timeframe={timeframe}
      onTimeframeChange={onTimeframeChange}
      loading={loading}
      dateTo={apiDateParams.date_to}
      compareDateTo={apiDateParams.previous_date_to}
    />
  )
}

// ------- Skeleton -------

function OverviewSkeleton({ config }: { config: PageConfig }) {
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
        if (w.type === 'chart') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12'}>
              <PanelSkeleton titleWidth="w-32">
                <ChartSkeleton height={256} showTitle={false} />
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
        if (w.type === 'registered') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12'}>
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
          )
        }
        return null
      })}
    </div>
  )
}
