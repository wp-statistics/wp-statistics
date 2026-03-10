/**
 * Overview Page Renderer
 *
 * Generic renderer for PHP-configured overview pages.
 * Handles: OverviewOptionsProvider wrapping, batch query,
 * widget grid rendering, skeleton loading.
 */

import { keepPreviousData, queryOptions, useQueries, useQuery } from '@tanstack/react-query'
import { useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { ExternalLink } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { BackButton } from '@/components/custom/back-button'
import { BarListContent } from '@/components/custom/bar-list-content'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { GlobalMap, type GlobalMapData } from '@/components/custom/global-map'
import { HorizontalBar } from '@/components/custom/horizontal-bar'
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
import { PostMetaBar, type TermInfo } from '@/components/custom/post-meta-bar'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import { SimpleTable, type SimpleTableColumn } from '@/components/custom/simple-table'
import { TabbedPanel, type TabbedPanelTab } from '@/components/custom/tabbed-panel'
import { WidgetCatalog } from '@/components/custom/widget-catalog'
import { WidgetContextMenu } from '@/components/custom/widget-context-menu'
import { NumericCell } from '@/components/data-table-columns'
import { getChannelDisplayName } from '@/components/data-table-columns/source-categories-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { type RegisteredWidget,useContentRegistry } from '@/contexts/content-registry-context'
import type { MetricConfig, WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { clientRequest } from '@/lib/client-request'
import { extractFilterField, getCompatibleFilters } from '@/lib/filter-utils'
import type { FixedDatePeriod } from '@/lib/fixed-date-ranges'
import { getFixedDatePeriods } from '@/lib/fixed-date-ranges'
import { type Timeframe,TIMEFRAME_TO_GROUP_BY } from '@/lib/response-helpers'
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

// Extract dynamic param name from a TanStack Router path (e.g., '/author/$authorId' → 'authorId')
function extractRouteParamName(routePath: string): string {
  return routePath.match(/\$(\w+)/)?.[1] || 'id'
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

// Stable reference for registered widget render props (avoids per-render lambda allocation)
const getTotalFromResponse = (t: Record<string, unknown> | undefined, key: string): number =>
  getTotalValue(t?.[key])

// ------- Batch query types -------

interface TrafficSummaryPeriodResponse {
  success: boolean
  items: {
    traffic_summary?: {
      success: boolean
      totals: Record<string, { current: number | string; previous?: number | string }>
    }
  }
}

interface TrafficSummaryRow {
  label: string
  values: Record<string, number>
  previousValues?: Record<string, number>
  comparisonLabel?: string
}

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

/** Config type accepted by the renderer: overview or detail */
type PageConfig = PhpOverviewDefinition | PhpDetailDefinition

/** Shared context passed to all widget renderers */
interface WidgetRenderContext {
  batchItems: Record<string, BatchQueryResult>
  isCompareEnabled: boolean
  comparisonDateLabel: string
  navigate: ReturnType<typeof useNavigate>
  calcPercentage: (current: number, previous: number) => { percentage: string; isNegative: boolean }
  isWidgetVisible: (id: string) => boolean
  isFetching: boolean
  // Chart-specific
  timeframe: Timeframe
  onTimeframeChange?: (tf: Timeframe) => void
  isChartRefetching: boolean
  apiDateParams: { date_from: string; date_to: string; previous_date_from?: string; previous_date_to?: string }
  // Map-specific
  mapDataByWidgetId: Record<string, GlobalMapData>
  isLoading: boolean
  // Traffic-summary-specific
  fixedDatePeriods: FixedDatePeriod[]
  trafficSummaryQueries: { data: unknown; isLoading: boolean }[]
  // Registered widgets
  registeredWidgets: RegisteredWidget[]
}

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
    /** Additional API filters merged at top level (e.g., { post_type: { is: 'post' } }) */
    apiFilters?: Record<string, Record<string, string | string[]>>
  }
) {
  const hasCompare = !!(params.compareDateFrom && params.compareDateTo)
  const transformedFilters =
    params.filters.length > 0
      ? transformFiltersToApi(params.filters as Parameters<typeof transformFiltersToApi>[0])
      : {}
  const mergedFilters = { ...transformedFilters, ...params.apiFilters }
  const hasFilters = Object.keys(mergedFilters).length > 0
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
    // eslint-disable-next-line @tanstack/query/exhaustive-deps -- mergedFilters is included conditionally, queries is static config
    queryKey: [
      config.pageId,
      params.entityFilter?.value || null,
      params.dateFrom,
      params.dateTo,
      params.compareDateFrom,
      params.compareDateTo,
      hasFilters ? mergedFilters : null,
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
          ...(hasFilters && { filters: mergedFilters }),
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
  title: titleOverride,
  routeParams,
  apiFilters,
  headerActions,
  pageFilters,
}: {
  config: PageConfig
  /** Override the PHP config title (e.g., dynamic taxonomy label) */
  title?: string
  routeParams?: Record<string, string>
  /** Additional API filters merged at top level (e.g., from PostTypeSelect) */
  apiFilters?: Record<string, Record<string, string | string[]>>
  /** Extra elements rendered in the detail page header (e.g., PostTypeSelect) */
  headerActions?: React.ReactNode
  /** Page-specific filter configs shown in the Options drawer (e.g., PostTypeSelect on mobile) */
  pageFilters?: import('@/components/custom/options-drawer').PageFilterConfig[]
}) {
  const widgetConfigs: WidgetConfig[] = useMemo(
    () =>
      config.widgets.map((w) => ({
        id: w.id,
        label: w.label,
        defaultVisible: w.defaultVisible ?? true,
        defaultSize: (w.defaultSize || 12) as 4 | 6 | 8 | 12,
        ...(w.allowedSizes && { allowedSizes: w.allowedSizes }),
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

  const overviewConfig = config.type === 'overview' ? config : undefined

  const optionsConfig: OverviewOptionsConfig = useMemo(
    () => ({
      pageId: config.pageId,
      filterGroup: config.filterGroup as FilterGroup,
      widgetConfigs,
      metricConfigs,
      hideFilters: config.hideFilters,
      ...(overviewConfig?.hideDateRange && { hideDateRange: true }),
      ...(pageFilters && { pageFilters }),
    }),
    [config.pageId, config.filterGroup, config.hideFilters, overviewConfig?.hideDateRange, widgetConfigs, metricConfigs, pageFilters]
  )

  return (
    <OverviewOptionsProvider config={optionsConfig}>
      <OverviewContent config={config} title={titleOverride} optionsConfig={optionsConfig} routeParams={routeParams} apiFilters={apiFilters} headerActions={headerActions} />
    </OverviewOptionsProvider>
  )
}

function OverviewContent({
  config,
  title: titleOverride,
  optionsConfig,
  routeParams,
  apiFilters: externalApiFilters,
  headerActions,
}: {
  config: PageConfig
  title?: string
  optionsConfig: OverviewOptionsConfig
  routeParams?: Record<string, string>
  apiFilters?: Record<string, Record<string, string | string[]>>
  headerActions?: React.ReactNode
}) {
  const { dateFrom, dateTo, compareDateFrom, compareDateTo, period, filters: appliedFilters, isInitialized, isCompareEnabled, apiDateParams, handleDateRangeUpdate, applyFilters: handleApplyFilters } = useGlobalFilters()
  const { isWidgetVisible, isMetricVisible, getWidgetSize, getOrderedVisibleWidgets } = usePageOptions()
  const options = useOverviewOptions(optionsConfig)
  const navigate = useNavigate()
  const { label: comparisonDateLabel } = useComparisonDateLabel()
  const calcPercentage = usePercentageCalc()

  // Get JS-registered widgets for this page (from premium modules or core)
  const { getWidgetsForPage } = useContentRegistry()
  const registeredWidgets = getWidgetsForPage(config.pageId)

  // Build entity filter for detail pages
  const detailConfig = config.type === 'detail' ? config : undefined
  const entityValue = detailConfig?.entityParam ? routeParams?.[detailConfig.entityParam] : undefined

  // Filter fields for filter button (detail pages or overview pages with showFilterButton)
  const wp = WordPress.getInstance()
  const overviewConfig = config.type === 'overview' ? config : undefined
  const needsFilterFields = detailConfig?.showFilterButton || overviewConfig?.showFilterButton
  const filterFields = useMemo<FilterField[]>(() => {
    if (!needsFilterFields) return []
    return wp.getFilterFieldsByGroup(config.filterGroup) as FilterField[]
  }, [needsFilterFields, config.filterGroup, wp])

  // Default filters (e.g., post_type=post on content/authors pages)
  const defaultFilterConfigs = overviewConfig?.defaultFilters
  const [defaultFiltersRemoved, setDefaultFiltersRemoved] = useState<Set<string>>(new Set())

  // Build display objects for default filters from PHP config + filter field definitions
  const defaultFilterDisplayObjects = useMemo(() => {
    if (!defaultFilterConfigs?.length || !filterFields.length) return []
    return defaultFilterConfigs.map((df) => {
      const field = filterFields.find((f) => f.name === df.field)
      const option = field?.options?.find((o) => String(o.value) === df.value)
      return {
        id: `${df.field}-default`,
        label: field?.label || df.field,
        operator: '=',
        rawOperator: df.operator,
        value: option?.label || df.value,
        rawValue: df.value,
      }
    })
  }, [defaultFilterConfigs, filterFields])

  // Compatible filters (global filters filtered to this page's filter group)
  const compatibleFilters = useMemo(() => {
    if (!filterFields.length) return appliedFilters || []
    return getCompatibleFilters(appliedFilters || [], filterFields as Parameters<typeof getCompatibleFilters>[1])
  }, [appliedFilters, filterFields])

  // Merge default filters with compatible filters for API and display
  const filtersForApi = useMemo(() => {
    if (!defaultFilterDisplayObjects.length) return compatibleFilters
    const userFieldNames = new Set(compatibleFilters.map((f) => extractFilterField(f.id)))
    const activeDefaults = defaultFilterDisplayObjects.filter(
      (df) => !userFieldNames.has(extractFilterField(df.id)) && !defaultFiltersRemoved.has(df.id)
    )
    return [...compatibleFilters, ...activeDefaults]
  }, [compatibleFilters, defaultFilterDisplayObjects, defaultFiltersRemoved])

  // Wrap applyFilters to detect default filter removal
  const handleApplyWithDefaults = useCallback(
    (newFilters: typeof appliedFilters) => {
      if (defaultFilterDisplayObjects.length) {
        // Check if any default filter was removed
        const newFieldNames = new Set((newFilters || []).map((f) => extractFilterField(f.id)))
        const hadFieldNames = new Set(filtersForApi.map((f) => extractFilterField(f.id)))
        for (const df of defaultFilterDisplayObjects) {
          const fieldName = extractFilterField(df.id)
          if (hadFieldNames.has(fieldName) && !newFieldNames.has(fieldName)) {
            setDefaultFiltersRemoved((prev) => new Set(prev).add(df.id))
          }
        }
        // Strip default filters before applying to global state
        const globalFilters = newFilters?.filter((f) => {
          const fieldName = extractFilterField(f.id)
          const rawValue = (f as Record<string, unknown>).rawValue ?? (f as Record<string, unknown>).value
          return !defaultFilterConfigs?.some((dc) => dc.field === fieldName && String(rawValue) === dc.value)
        }) ?? []
        handleApplyFilters(globalFilters)
      } else {
        handleApplyFilters(newFilters)
      }
    },
    [defaultFilterDisplayObjects, defaultFilterConfigs, filtersForApi, handleApplyFilters]
  )

  // Reset removed state when user adds a filter for a default field
  useEffect(() => {
    if (!defaultFilterDisplayObjects.length) return
    const userFieldNames = new Set(compatibleFilters.map((f) => extractFilterField(f.id)))
    for (const df of defaultFilterDisplayObjects) {
      if (userFieldNames.has(extractFilterField(df.id)) && defaultFiltersRemoved.has(df.id)) {
        setDefaultFiltersRemoved((prev) => {
          const next = new Set(prev)
          next.delete(df.id)
          return next
        })
      }
    }
  }, [compatibleFilters, defaultFilterDisplayObjects, defaultFiltersRemoved])

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

  const entityFilter = useMemo(
    () => detailConfig && entityValue
      ? { key: detailConfig.filterField, operator: 'is', value: entityValue }
      : undefined,
    [detailConfig, entityValue]
  )

  // Traffic summary: parallel fixed-period queries (independent from main batch)
  const trafficSummaryWidget = useMemo(
    () => config.widgets.find((w) => w.type === 'traffic-summary'),
    [config.widgets]
  )
  const fixedDatePeriods = useMemo(
    () => (trafficSummaryWidget ? getFixedDatePeriods() : []),
    [trafficSummaryWidget]
  )
  const tsApiFilters = useMemo(() => {
    if (!trafficSummaryWidget) return {}
    const filters = defaultFilterConfigs?.length ? filtersForApi : (appliedFilters || [])
    const transformed =
      filters.length > 0
        ? transformFiltersToApi(filters as Parameters<typeof transformFiltersToApi>[0])
        : {}
    return { ...transformed, ...externalApiFilters }
  }, [trafficSummaryWidget, appliedFilters, filtersForApi, defaultFilterConfigs, externalApiFilters])

  const trafficSummaryQueryDefs = useMemo(
    () =>
      fixedDatePeriods.map((period) => {
        const hasCompare = !!(period.compareDateFrom && period.compareDateTo)
        const tsConfig = trafficSummaryWidget?.trafficSummaryConfig
        const hasFilters = Object.keys(tsApiFilters).length > 0
        return {
          queryKey: [config.pageId, 'traffic-summary', entityFilter, period.id, period.dateFrom, period.dateTo, period.compareDateFrom, period.compareDateTo, tsApiFilters],
          queryFn: () =>
            clientRequest.post<TrafficSummaryPeriodResponse>(
              '',
              {
                date_from: period.dateFrom,
                date_to: period.dateTo,
                compare: hasCompare,
                ...(hasCompare && {
                  previous_date_from: period.compareDateFrom,
                  previous_date_to: period.compareDateTo,
                }),
                ...(hasFilters && { filters: tsApiFilters }),
                queries: [
                  {
                    id: 'traffic_summary',
                    sources: tsConfig?.sources || ['visitors', 'views'],
                    group_by: [],
                    ...(entityFilter && { filters: [entityFilter] }),
                    format: 'flat',
                    show_totals: true,
                    compare: hasCompare,
                  },
                ],
              },
              { params: { action: WordPress.getInstance().getAnalyticsAction() } }
            ),
          staleTime: 5 * 60 * 1000,
          enabled: isInitialized && !!trafficSummaryWidget,
          placeholderData: keepPreviousData,
        }
      }),
    [fixedDatePeriods, trafficSummaryWidget, tsApiFilters, entityFilter, isInitialized, config.pageId]
  )
  const trafficSummaryQueries = useQueries({ queries: trafficSummaryQueryDefs })

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
      filters: defaultFilterConfigs?.length ? filtersForApi : (appliedFilters || []),
      timeframe: supportsTimeframe ? timeframe : undefined,
      entityFilter,
      apiFilters: externalApiFilters,
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

  // Extract entity metadata row (for PostMetaBar on content detail pages)
  // Also serves as source for entityTitleFromQuery when entityInfo.queryId matches
  const entityMetaRow = useMemo((): Record<string, unknown> | null => {
    const queryId = detailConfig?.entityMeta?.queryId ?? detailConfig?.entityInfo?.queryId
    if (!queryId) return null
    const result = batchResponse?.data?.items?.[queryId]
    const rows = result?.data?.rows || result?.items
    return (Array.isArray(rows) ? (rows[0] as Record<string, unknown>) : null) ?? null
  }, [batchResponse, detailConfig])

  // Extract entity display name from response (for detail pages)
  const entityTitleFromQuery = useMemo(() => {
    if (!detailConfig?.entityInfo?.nameField || !entityMetaRow) return null
    const name = entityMetaRow[detailConfig.entityInfo.nameField] as string
    if (name) return name
    // Fall back to alternate field (e.g., page_uri when page_title is empty)
    if (detailConfig.entityInfo.nameFallbackField) {
      return (entityMetaRow[detailConfig.entityInfo.nameFallbackField] as string) || null
    }
    return null
  }, [entityMetaRow, detailConfig])

  // AJAX fallback for entity name when analytics data is empty (e.g., new category with no traffic)
  const entityInfoFallback = detailConfig?.entityInfo
  const { data: entityInfoFallbackResponse } = useQuery({
    queryKey: [config.pageId, 'entity-info-fallback', entityValue, entityInfoFallback],
    queryFn: () =>
      clientRequest.post<{ success: boolean; data: Record<string, string> }>(
        '',
        { [entityInfoFallback!.fallbackParam || 'id']: entityValue! },
        { params: { action: entityInfoFallback!.fallbackAction! } }
      ),
    staleTime: Infinity,
    enabled: !!entityValue && !!entityInfoFallback?.fallbackAction && !entityTitleFromQuery,
  })

  const entityTitle = useMemo(() => {
    if (entityTitleFromQuery) return entityTitleFromQuery
    if (entityInfoFallback?.fallbackNameField && entityInfoFallbackResponse?.data?.data) {
      return entityInfoFallbackResponse.data.data[entityInfoFallback.fallbackNameField] || config.title
    }
    return config.title
  }, [entityTitleFromQuery, entityInfoFallback, entityInfoFallbackResponse, config.title])

  // Title badge (e.g., page type label for URL pages) — reads from entityMetaRow
  const titleBadgeLabel = (() => {
    if (!detailConfig?.titleBadge || !entityMetaRow) return null
    const value = String(entityMetaRow[detailConfig.titleBadge.field] || '')
    return detailConfig.titleBadge.labels[value] || value || null
  })()

  // External link (permalink for URL pages) — reads from entityMetaRow
  const externalLinkUrl = (!detailConfig?.externalLink || !entityMetaRow)
    ? null
    : (entityMetaRow[detailConfig.externalLink.field] as string) || null

  // Content redirect guard: redirect linkable content types to content report
  const contentRedirectConfig = detailConfig?.contentRedirect
  useEffect(() => {
    if (showSkeleton || !contentRedirectConfig || !entityMetaRow) return
    const wpId = entityMetaRow[contentRedirectConfig.wpIdField] as number | null
    const pageType = String(entityMetaRow[contentRedirectConfig.typeField] || 'unknown')
    if (wpId && !contentRedirectConfig.excludeTypes.includes(pageType)) {
      navigate({
        to: contentRedirectConfig.targetRoute,
        params: { [contentRedirectConfig.targetParam]: String(wpId) },
        replace: true,
      })
    }
  }, [showSkeleton, contentRedirectConfig, entityMetaRow, navigate])

  // Widget ordering and sizing (supports WidgetCatalog reordering on overview pages)
  const orderedWidgets = useMemo(() => {
    if (!overviewConfig?.widgetCategories) {
      return config.widgets.filter((w) => isWidgetVisible(w.id))
    }
    const widgetMap = new Map(config.widgets.map((w) => [w.id, w]))
    return getOrderedVisibleWidgets()
      .map((wc) => widgetMap.get(wc.id))
      .filter((w): w is PhpOverviewWidget => !!w)
  }, [overviewConfig?.widgetCategories, config.widgets, isWidgetVisible, getOrderedVisibleWidgets])

  // Shared context for widget renderers (not memoized — consumed in same render cycle)
  const widgetCtx: WidgetRenderContext = {
    batchItems: batchResponse?.data?.items || {},
    isCompareEnabled,
    comparisonDateLabel,
    navigate,
    calcPercentage,
    isWidgetVisible,
    isFetching,
    timeframe,
    onTimeframeChange: supportsTimeframe ? handleTimeframeChange : undefined,
    isChartRefetching,
    apiDateParams,
    mapDataByWidgetId,
    isLoading,
    fixedDatePeriods,
    trafficSummaryQueries,
    registeredWidgets,
  }

  return (
    <div className="min-w-0">
      {detailConfig ? (
        <div className="px-4 py-3">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3 min-w-0">
              <div className="contents" data-pdf-hide>
                <BackButton defaultTo={detailConfig.backLink || '/'} label={detailConfig.backLabel} />
              </div>
              <h1 className="text-2xl font-semibold text-neutral-800 truncate max-w-[400px]" title={entityTitle}>
                {showSkeleton ? __('Loading...', 'wp-statistics') : entityTitle}
              </h1>
              {!showSkeleton && titleBadgeLabel && (
                <span className="inline-flex items-center rounded-md bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 shrink-0">
                  {titleBadgeLabel}
                </span>
              )}
              {!showSkeleton && externalLinkUrl && (
                <a
                  href={externalLinkUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="p-1 rounded-md hover:bg-neutral-100 transition-colors shrink-0"
                  aria-label={__('Open page', 'wp-statistics')}
                >
                  <ExternalLink className="h-4 w-4 text-neutral-400" />
                </a>
              )}
            </div>
            <div className="flex items-center gap-3" data-pdf-hide>
              {detailConfig.showFilterButton && filterFields.length > 0 && isInitialized && (
                <div className="hidden lg:flex">
                  <FilterButton
                    fields={filterFields}
                    appliedFilters={appliedFilters || []}
                    onApplyFilters={handleApplyFilters}
                    filterGroup={config.filterGroup}
                  />
                </div>
              )}
              {headerActions}
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
          {!showSkeleton && entityMetaRow && (
            <PostMetaBar
              authorName={entityMetaRow.author_name as string}
              postTypeLabel={entityMetaRow.post_type_label as string}
              publishedDate={entityMetaRow.published_date as string}
              modifiedDate={entityMetaRow.modified_date as string}
              terms={entityMetaRow.cached_terms as TermInfo[]}
              className="mt-2 ml-9"
            />
          )}
        </div>
      ) : overviewConfig?.widgetCategories ? (
        <div className="px-4 py-3">
          <div className="flex items-center justify-between">
            <h1 className="text-2xl font-semibold text-neutral-800">
              {titleOverride || config.title}
            </h1>
            <div className="flex items-center gap-3" data-pdf-hide>
              <WidgetCatalog categories={overviewConfig.widgetCategories} />
              <OptionsDrawerTrigger {...options.triggerProps} />
            </div>
          </div>
        </div>
      ) : (
        <ReportPageHeader
          title={titleOverride || config.title}
          filterGroup={config.filterGroup as FilterGroup}
          optionsTriggerProps={options.triggerProps}
          showFilterButton={config.showFilterButton ?? !config.hideFilters}
          {...(defaultFilterConfigs?.length && {
            overrideAppliedFilters: filtersForApi,
            overrideApplyFilters: handleApplyWithDefaults,
          })}
        >
          {headerActions}
        </ReportPageHeader>
      )}

      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-3" currentRoute={config.pageId} />

        {showSkeleton ? (
          <OverviewSkeleton config={config} />
        ) : (
          <>
            <div className="grid gap-3 grid-cols-12">
              {orderedWidgets.map((widget) => {
                const size = getWidgetSize(widget.id)
                const colSpan = COL_SPAN[size] || COL_SPAN[widget.defaultSize] || 'col-span-12'
                const contextMenu = widget.allowedSizes
                  ? <WidgetContextMenu widgetId={widget.id} allowedSizes={widget.allowedSizes} />
                  : undefined

                return renderWidget(widget, {
                  colSpan,
                  contextMenu,
                  overviewMetrics,
                  ctx: widgetCtx,
                })
              })}
            </div>

            {overviewConfig?.widgetCategories && orderedWidgets.length < config.widgets.length && (
              <div className="mt-4 flex justify-center" data-pdf-hide>
                <WidgetCatalog categories={overviewConfig.widgetCategories} />
              </div>
            )}
          </>
        )}
      </div>
    </div>
  )
}

// ------- Widget Renderers -------

/** Dispatch a widget to its renderer. Returns null for unknown types. */
function renderWidget(
  widget: PhpOverviewWidget,
  opts: {
    colSpan: string
    contextMenu: React.ReactNode | undefined
    overviewMetrics: Array<Record<string, unknown>>
    ctx: WidgetRenderContext
  },
): React.ReactNode {
  const { colSpan, contextMenu, overviewMetrics, ctx } = opts

  switch (widget.type) {
    case 'metrics': {
      if (overviewMetrics.length === 0) return null
      return (
        <div key={widget.id} className={colSpan}>
          <Panel className="h-full">
            {contextMenu && (
              <div className="flex items-center justify-end px-4 pt-3 pb-1">
                {contextMenu}
              </div>
            )}
            <Metrics metrics={overviewMetrics} columns={contextMenu ? 'auto' : undefined} />
          </Panel>
        </div>
      )
    }

    case 'chart':
      if (!widget.queryId || !widget.chartConfig) return null
      return (
        <div key={widget.id} className={colSpan}>
          <ChartWidget
            widget={widget}
            queryResult={ctx.batchItems[widget.queryId]}
            isCompareEnabled={ctx.isCompareEnabled}
            timeframe={ctx.timeframe}
            onTimeframeChange={widget.chartConfig.timeframeSupport ? ctx.onTimeframeChange : undefined}
            loading={ctx.isChartRefetching}
            apiDateParams={ctx.apiDateParams}
            headerRight={contextMenu}
          />
        </div>
      )

    case 'map':
      if (!widget.mapConfig) return null
      return (
        <div key={widget.id} className={colSpan}>
          <MapWidget widget={widget} ctx={ctx} />
        </div>
      )

    case 'bar-list':
      if (!widget.queryId) return null
      return (
        <div key={widget.id} className={colSpan}>
          <BarListWidget widget={widget} ctx={ctx} contextMenu={contextMenu} />
        </div>
      )

    case 'tabbed-bar-list':
      if (!widget.queryId || !widget.tabbedBarListConfig) return null
      return (
        <div key={widget.id} className={colSpan}>
          <TabbedBarListWidget
            widget={widget}
            rows={(ctx.batchItems[widget.queryId]?.data?.rows || []) as Record<string, unknown>[]}
            isCompareEnabled={ctx.isCompareEnabled}
            calcPercentage={ctx.calcPercentage}
            comparisonDateLabel={ctx.comparisonDateLabel}
          />
        </div>
      )

    case 'traffic-summary':
      if (!widget.trafficSummaryConfig) return null
      return (
        <div key={widget.id} className={colSpan}>
          <TrafficSummaryWidget
            widget={widget}
            periods={ctx.fixedDatePeriods}
            queries={ctx.trafficSummaryQueries}
          />
        </div>
      )

    case 'registered':
      return <RegisteredWidgetsRenderer key={widget.id} colSpan={colSpan} ctx={ctx} />

    default:
      return null
  }
}

// ------- Map Widget -------

function MapWidget({ widget, ctx }: { widget: PhpOverviewWidget; ctx: WidgetRenderContext }) {
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

// ------- Bar-List Widget -------

function BarListWidget({
  widget,
  ctx,
  contextMenu,
}: {
  widget: PhpOverviewWidget
  ctx: WidgetRenderContext
  contextMenu?: React.ReactNode
}) {
  const navigate = ctx.navigate
  const queryResult = ctx.batchItems[widget.queryId!]
  const rows = queryResult?.data?.rows || []
  const totals = queryResult?.data?.totals

  const linkResolvers = useMemo(() => {
    if (widget.linkType === 'analytics-route') {
      // Cache per-item route lookup to avoid calling getAnalyticsRoute twice per row
      let lastItem: Record<string, unknown> | null = null
      let lastRoute: ReturnType<typeof getAnalyticsRoute> = undefined
      const resolveRoute = (item: Record<string, unknown>) => {
        if (item !== lastItem) {
          lastItem = item
          lastRoute = getAnalyticsRoute(item.page_type as string, item.page_wp_id as number, undefined, item.resource_id as number)
        }
        return lastRoute
      }
      return {
        linkTo: (item: Record<string, unknown>) => resolveRoute(item)?.to,
        linkParams: (item: Record<string, unknown>) => resolveRoute(item)?.params,
      }
    }
    if (widget.linkTo && widget.linkParamField) {
      const paramName = extractRouteParamName(widget.linkTo)
      return {
        linkTo: (item: Record<string, unknown>) => item[widget.linkParamField!] ? widget.linkTo! : undefined,
        linkParams: (item: Record<string, unknown>) => {
          const value = String(item[widget.linkParamField!] || '').toLowerCase()
          return { [paramName]: value }
        },
      }
    }
    return {}
  }, [widget.linkType, widget.linkTo, widget.linkParamField])

  return (
    <HorizontalBarList
      title={widget.label}
      showComparison={ctx.isCompareEnabled}
      columnHeaders={widget.columnHeaders}
      items={transformToBarList(rows, {
        label: (item) => {
          let raw = widget.labelField ? item[widget.labelField] : undefined
          if (!raw && widget.labelFallbackFields) {
            for (const field of widget.labelFallbackFields) {
              raw = item[field]
              if (raw) break
            }
          }
          const label = String(raw || __('Unknown', 'wp-statistics'))
          return widget.labelTransform ? LABEL_TRANSFORMS[widget.labelTransform](label) : label
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
            ? (item) => ICON_RENDERERS[widget.iconType!](item as Record<string, unknown>, widget.iconSlugField!)
            : undefined,
        isCompareEnabled: ctx.isCompareEnabled,
        comparisonDateLabel: ctx.comparisonDateLabel,
        ...linkResolvers,
      })}
      link={widget.link ? { action: () => navigate({ to: widget.link!.to }) } : undefined}
      headerRight={contextMenu}
    />
  )
}

// ------- Registered Widgets -------

function RegisteredWidgetsRenderer({ colSpan, ctx }: { colSpan: string; ctx: WidgetRenderContext }) {
  return (
    <>
      {ctx.registeredWidgets.map((rw) => {
        if (!ctx.isWidgetVisible(rw.id)) return null
        const widgetData = ctx.batchItems[rw.queryId]
        const data = (widgetData as { data?: { rows?: unknown[] } })?.data?.rows || []
        const totals = (widgetData as { data?: { totals?: Record<string, unknown> } })?.data?.totals || {}
        return (
          <div key={rw.id} className={colSpan}>
            {rw.render({
              data,
              totals,
              isCompareEnabled: ctx.isCompareEnabled,
              comparisonDateLabel: ctx.comparisonDateLabel,
              isFetching: ctx.isFetching,
              navigate: ctx.navigate,
              getTotalFromResponse,
            })}
          </div>
        )
      })}
    </>
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
  headerRight,
}: {
  widget: PhpOverviewWidget
  queryResult: BatchQueryResult | undefined
  isCompareEnabled: boolean
  timeframe: Timeframe
  onTimeframeChange?: (tf: Timeframe) => void
  loading: boolean
  apiDateParams: { date_to: string; previous_date_to?: string }
  headerRight?: React.ReactNode
}) {
  const { data: chartData, metrics } = useChartData(queryResult, {
    metrics: widget.chartConfig!.metrics.map((m) => ({
      key: m.key,
      label: m.label,
      color: m.color,
      ...(m.type && { type: m.type }),
    })),
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  return (
    <LineChart
      className="h-full"
      title={widget.label}
      data={chartData}
      metrics={metrics}
      showPreviousPeriod={isCompareEnabled}
      timeframe={timeframe}
      onTimeframeChange={onTimeframeChange}
      loading={loading}
      dateTo={apiDateParams.date_to}
      compareDateTo={apiDateParams.previous_date_to}
      headerRight={headerRight}
    />
  )
}

// ------- Tabbed Bar-List Widget -------

function TabbedBarListWidget({
  widget,
  rows,
  isCompareEnabled,
  calcPercentage,
  comparisonDateLabel,
}: {
  widget: PhpOverviewWidget
  rows: Record<string, unknown>[]
  isCompareEnabled: boolean
  calcPercentage: (current: number, previous: number) => { percentage: string; isNegative: boolean }
  comparisonDateLabel: string
}) {
  const config = widget.tabbedBarListConfig!
  const labelField = config.labelField || 'page_title'
  const labelFallback = config.labelFallbackField || 'page_uri'

  const tabs = useMemo((): TabbedPanelTab[] => {
    return config.tabs
      .map((tabConfig) => {
        let tabRows = [...rows]

        // Filter rows if specified (e.g., comments > 0)
        if (tabConfig.filterField && tabConfig.filterMinValue !== undefined) {
          tabRows = tabRows.filter((row) => Number(row[tabConfig.filterField!]) >= tabConfig.filterMinValue!)
        }

        // Skip tab entirely if filtered to empty
        if (tabRows.length === 0 && tabConfig.filterField) return null

        // Compute ratio values when computedField is set
        const hasComputed = !!tabConfig.computedField
        if (hasComputed) {
          tabRows = tabRows.map((row) => ({
            ...row,
            _computed: Number(row[tabConfig.computedField!.denominator]) > 0
              ? Number(row[tabConfig.computedField!.numerator]) / Number(row[tabConfig.computedField!.denominator])
              : 0,
          }))
        }

        // Sort rows (use _computed for computed fields when sortBy matches)
        const sortField = hasComputed && tabConfig.sortBy === '_computed' ? '_computed' : tabConfig.sortBy
        tabRows.sort((a, b) => {
          let aVal: number, bVal: number
          if (tabConfig.sortType === 'date') {
            aVal = new Date(String(a[sortField] || 0)).getTime()
            bVal = new Date(String(b[sortField] || 0)).getTime()
          } else {
            aVal = Number(a[sortField]) || 0
            bVal = Number(b[sortField]) || 0
          }
          return tabConfig.sortDesc === false ? aVal - bVal : bVal - aVal
        })

        tabRows = tabRows.slice(0, tabConfig.maxItems || 5)

        return {
          id: tabConfig.id,
          label: tabConfig.label,
          columnHeaders: tabConfig.columnHeaders,
          ...(tabConfig.link && { link: tabConfig.link }),
          content: (
            <BarListContent isEmpty={tabRows.length === 0}>
              {tabRows.map((item, idx) => {
                // Resolve value: computed ratio or direct field
                const value = hasComputed ? (Number(item._computed) || 0) : (Number(item[tabConfig.valueField]) || 0)
                const prevValue = Number((item.previous as Record<string, unknown>)?.[tabConfig.valueField]) || 0
                const showComp = tabConfig.showComparison !== false && isCompareEnabled && !hasComputed
                const comparison = showComp ? calcPercentage(value, prevValue) : null

                // Resolve per-item link
                let linkTo: string | undefined
                let linkParams: Record<string, string> | undefined
                if (config.linkType === 'analytics-route') {
                  const route = getAnalyticsRoute(item.page_type as string, item.page_wp_id as number)
                  linkTo = route?.to
                  linkParams = route?.params
                } else if (config.linkTo && config.linkParamField) {
                  linkTo = config.linkTo
                  linkParams = { [extractRouteParamName(config.linkTo)]: String(item[config.linkParamField] || '') }
                }

                // Resolve icon (author avatar)
                let icon: React.ReactNode | undefined
                if (config.iconType === 'author-avatar' && config.iconField) {
                  const avatarUrl = String(item[config.iconField] || '')
                  icon = avatarUrl
                    ? <img src={avatarUrl} alt="" className="h-6 w-6 rounded-full object-cover" />
                    : <div className="h-6 w-6 rounded-full bg-neutral-200" />
                }

                const label = String(item[labelField] || item[labelFallback] || '/')
                const formatFn = tabConfig.valueFormat === 'decimal' ? formatDecimal : formatCompactNumber
                const valueLabel = tabConfig.valueSuffix
                  ? `${formatFn(value)} ${tabConfig.valueSuffix}`
                  : formatFn(value)

                return (
                  <HorizontalBar
                    key={`${String(item[labelFallback] || item[labelField] || idx)}-${idx}`}
                    icon={icon}
                    label={label}
                    value={valueLabel}
                    percentage={comparison?.percentage}
                    isNegative={comparison?.isNegative}
                    tooltipSubtitle={
                      showComp ? `${__('Previous:', 'wp-statistics')} ${formatCompactNumber(prevValue)}` : undefined
                    }
                    comparisonDateLabel={comparisonDateLabel}
                    showComparison={showComp}
                    showBar={false}
                    highlightFirst={false}
                    linkTo={linkTo}
                    linkParams={linkParams}
                  />
                )
              })}
            </BarListContent>
          ),
        }
      })
      .filter((t): t is TabbedPanelTab => t !== null)
  }, [rows, config, isCompareEnabled, calcPercentage, comparisonDateLabel, labelField, labelFallback])

  return <TabbedPanel title={widget.label} tabs={tabs} defaultTab={config.tabs[0]?.id} />
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
        if (w.type === 'tabbed-bar-list') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12'}>
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'traffic-summary') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12 lg:col-span-4'}>
              <PanelSkeleton titleWidth="w-32">
                <MetricsSkeleton count={5} columns={3} />
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

// ------- Traffic Summary Widget -------

function TrafficSummaryWidget({
  widget,
  periods,
  queries,
}: {
  widget: PhpOverviewWidget
  periods: FixedDatePeriod[]
  queries: { data: unknown; isLoading: boolean }[]
}) {
  const config = widget.trafficSummaryConfig!
  const isLoading = queries.some((q) => q.isLoading)

  const columns = useMemo(
    (): SimpleTableColumn<TrafficSummaryRow>[] => [
      {
        key: 'period',
        header: __('Time Period', 'wp-statistics'),
        cell: (row) => <span className="font-medium">{row.label}</span>,
      },
      ...config.metrics.map((metric) => ({
        key: metric.key,
        header: metric.label,
        align: 'right' as const,
        cell: (row: TrafficSummaryRow) => (
          <NumericCell
            value={row.values[metric.key] || 0}
            previousValue={row.previousValues?.[metric.key]}
            comparisonLabel={row.comparisonLabel}
          />
        ),
      })),
    ],
    [config.metrics]
  )

  const data = useMemo(
    (): TrafficSummaryRow[] =>
      periods.map((period, i) => {
        const response = queries[i]?.data as
          | { data?: TrafficSummaryPeriodResponse }
          | undefined
        const totals = response?.data?.items?.traffic_summary?.totals
        const values: Record<string, number> = {}
        const previousValues: Record<string, number> = {}

        for (const metric of config.metrics) {
          const val = totals?.[metric.key]
          values[metric.key] = Number(val?.current) || 0
          if (period.id !== 'total' && val?.previous !== undefined) {
            previousValues[metric.key] = Number(val.previous) || 0
          }
        }

        return {
          label: period.label,
          values,
          previousValues:
            Object.keys(previousValues).length > 0 ? previousValues : undefined,
          comparisonLabel: period.comparisonLabel,
        }
      }),
     
    [periods, queries, config.metrics]
  )

  return (
    <SimpleTable title={widget.label} columns={columns} data={data} isLoading={isLoading} />
  )
}
