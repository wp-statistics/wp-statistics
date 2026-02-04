/**
 * Single Visitor Report Page
 *
 * Shows detailed analytics for individual visitors including:
 * - Visitor profile (varies by type: user, IP, or hash)
 * - Key metrics (sessions, views, bounce rate, duration, pages/session)
 * - Activity timeline chart
 * - Session history with expandable page journeys
 * - Top pages visited
 * - Referral sources
 *
 * Route: /visitor/{type}/{id}
 * - type: 'user' | 'ip' | 'hash'
 * - id: WordPress user ID, IP address, or visitor hash
 */

import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef, Row, Table, VisibilityState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ChevronDown, ChevronRight, Globe, Hash } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { BackButton } from '@/components/custom/back-button'
import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import {
  ColumnsDetailView,
  ColumnsMenuEntry,
  DateRangeDetailView,
  DateRangeMenuEntry,
  MetricsDetailView,
  MetricsMenuEntry,
  OptionsDrawer,
  OptionsDrawerTrigger,
  type OverviewOptionsConfig,
  OverviewOptionsProvider,
  useOverviewOptions,
  WidgetsDetailView,
  WidgetsMenuEntry,
} from '@/components/custom/options-drawer'
import { DurationCell, EntryPageCell, NumericCell, PageCell, ReferrerCell } from '@/components/data-table-columns'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { pickMetrics } from '@/constants/metric-definitions'
import { type WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import {
  clearCachedColumns,
  getCachedVisibility,
  getCachedVisibleColumns,
  getVisibleColumnsForSave,
  setCachedColumns,
} from '@/lib/column-utils'
import { formatReferrerChannel } from '@/lib/filter-utils'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { resetUserPreferences, saveUserPreferences } from '@/services/user-preferences'
import {
  getSessionPageViewsQueryOptions,
  getSingleVisitorQueryOptions,
  type HashVisitorInfo,
  type IpVisitorInfo,
  type PageViewRow,
  type ReferrerRow,
  type SessionRow,
  type TopPageRow,
  type UserVisitorInfo,
  type VisitorInfo,
  type VisitorType,
} from '@/services/visitor-insight/get-single-visitor'

export const Route = createLazyFileRoute('/(visitor-insights)/visitor_/$type/$id')({
  component: RouteComponent,
})

// Widget configuration for Single Visitor Report
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true },
  { id: 'activity-timeline', label: __('Activity Timeline', 'wp-statistics'), defaultVisible: true },
  { id: 'session-history', label: __('Session History', 'wp-statistics'), defaultVisible: true },
  { id: 'top-pages', label: __('Top Pages Visited', 'wp-statistics'), defaultVisible: true },
  { id: 'top-referrers', label: __('Referral Sources', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for Single Visitor Report
const METRIC_CONFIGS = pickMetrics('sessions', 'views', 'bounceRate', 'sessionDuration', 'pagesPerSession')

// Base options configuration
const BASE_OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'single-visitor',
  filterGroup: 'individual-visitor',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
  hideFilters: true,
}

// Default hidden columns for session history table (all visible by default)
const SESSION_DEFAULT_HIDDEN_COLUMNS: string[] = []

// Context identifier for session history column preferences
const SESSION_HISTORY_CONTEXT = 'single-visitor-sessions'

/**
 * Custom Options Drawer for Single Visitor page
 * Includes: DateRange, Widgets, Metrics, and Columns for the session history table
 */
function SingleVisitorOptionsDrawer({
  isOpen,
  setIsOpen,
  resetToDefaults,
  sessionTableRef,
  onColumnVisibilityChange,
  onColumnOrderChange,
  onColumnReset,
}: {
  isOpen: boolean
  setIsOpen: (open: boolean) => void
  resetToDefaults: () => void
  sessionTableRef: React.MutableRefObject<Table<SessionRow> | null>
  onColumnVisibilityChange: (visibility: VisibilityState) => void
  onColumnOrderChange: (order: string[]) => void
  onColumnReset: () => void
}) {
  return (
    <OptionsDrawer open={isOpen} onOpenChange={setIsOpen} onReset={resetToDefaults}>
      {/* Main menu entries */}
      <DateRangeMenuEntry />
      <WidgetsMenuEntry />
      <MetricsMenuEntry />
      <ColumnsMenuEntry
        table={sessionTableRef.current}
        defaultHiddenColumns={SESSION_DEFAULT_HIDDEN_COLUMNS}
      />

      {/* Detail views */}
      <DateRangeDetailView />
      <WidgetsDetailView />
      <MetricsDetailView />
      <ColumnsDetailView
        table={sessionTableRef.current}
        defaultHiddenColumns={SESSION_DEFAULT_HIDDEN_COLUMNS}
        onColumnVisibilityChange={onColumnVisibilityChange}
        onColumnOrderChange={onColumnOrderChange}
        onReset={onColumnReset}
      />
    </OptionsDrawer>
  )
}

function RouteComponent() {
  const { type, id } = Route.useParams()

  // Validate type parameter
  const validTypes = ['user', 'ip', 'hash'] as const
  const visitorType = validTypes.includes(type as (typeof validTypes)[number])
    ? (type as VisitorType)
    : null

  if (!visitorType) {
    return (
      <div className="min-w-0">
        <div className="flex items-center justify-between px-4 py-3">
          <h1 className="text-2xl font-semibold text-neutral-800">
            {__('Invalid Visitor Type', 'wp-statistics')}
          </h1>
        </div>
        <div className="p-3">
          <Panel className="p-8 text-center">
            <p className="text-sm text-muted-foreground">
              {__('The visitor type must be "user", "ip", or "hash".', 'wp-statistics')}
            </p>
          </Panel>
        </div>
      </div>
    )
  }

  return (
    <OverviewOptionsProvider config={BASE_OPTIONS_CONFIG}>
      <SingleVisitorReportContent type={visitorType} id={id} />
    </OverviewOptionsProvider>
  )
}

interface SingleVisitorReportContentProps {
  type: VisitorType
  id: string
}

function SingleVisitorReportContent({ type, id }: SingleVisitorReportContentProps) {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    handleDateRangeUpdate,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  // Page options for widget/metric visibility
  const { isWidgetVisible, isMetricVisible } = usePageOptions()

  // Options drawer
  const options = useOverviewOptions(BASE_OPTIONS_CONFIG)

  // Table ref for session history (needed for column management in Options drawer)
  const sessionTableRef = useRef<Table<SessionRow> | null>(null)

  // Session table column IDs (excluding expand which is not hideable)
  const sessionColumnIds = useMemo(
    () => ['session_start', 'session_duration', 'page_count', 'entry_page', 'exit_page', 'referrer_domain'],
    []
  )

  // Session column preferences (persistence)
  const initialSessionColumnVisibility = useMemo(() => {
    return getCachedVisibility(SESSION_HISTORY_CONTEXT, sessionColumnIds)
  }, [sessionColumnIds])

  const [sessionColumnOrder, setSessionColumnOrder] = useState<string[]>(() => {
    return getCachedVisibleColumns(SESSION_HISTORY_CONTEXT) || []
  })

  // Handle session column visibility change - save to localStorage + backend
  const handleSessionColumnVisibilityChange = useCallback(
    (visibility: VisibilityState) => {
      const visibleColumns = getVisibleColumnsForSave(visibility, sessionColumnOrder, sessionColumnIds)
      setCachedColumns(SESSION_HISTORY_CONTEXT, visibleColumns)
      saveUserPreferences({ context: SESSION_HISTORY_CONTEXT, columns: visibleColumns })
    },
    [sessionColumnOrder, sessionColumnIds]
  )

  // Handle session column order change
  const handleSessionColumnOrderChange = useCallback(
    (order: string[]) => {
      setSessionColumnOrder(order)
      // Order is embedded in visibility save - get current visibility from table
      const table = sessionTableRef.current
      if (table) {
        const visibility = table.getState().columnVisibility
        const visibleColumns = getVisibleColumnsForSave(visibility, order, sessionColumnIds)
        setCachedColumns(SESSION_HISTORY_CONTEXT, visibleColumns)
        saveUserPreferences({ context: SESSION_HISTORY_CONTEXT, columns: visibleColumns })
      }
    },
    [sessionColumnIds]
  )

  // Handle session column reset
  const handleSessionColumnPreferencesReset = useCallback(() => {
    clearCachedColumns(SESSION_HISTORY_CONTEXT)
    resetUserPreferences({ context: SESSION_HISTORY_CONTEXT })
    setSessionColumnOrder([])
    // Force table to reset visibility
    sessionTableRef.current?.resetColumnVisibility()
  }, [])

  // Chart timeframe state (local, not synced to URL)
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Track if only timeframe changed (for loading behavior)
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  // Detect what changed when data updates
  useEffect(() => {
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    // If dates changed, it's NOT a timeframe-only change
    if (dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    // Update refs
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [dateFrom, dateTo])

  // Custom timeframe setter that tracks the change type
  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  // Batch query for single visitor data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getSingleVisitorQueryOptions({
      type,
      id,
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      sessionsPage: 1,
      sessionsPerPage: 10,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Extract data from batch response
  const visitorInfoResponse = batchResponse?.data?.items?.visitor_info
  const visitorMetricsResponse = batchResponse?.data?.items?.visitor_metrics
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  const sessionsResponse = batchResponse?.data?.items?.sessions
  const topPagesResponse = batchResponse?.data?.items?.top_pages
  const topReferrersResponse = batchResponse?.data?.items?.top_referrers

  // Get visitor info
  const visitorInfo = visitorInfoResponse?.data?.rows?.[0] as VisitorInfo | undefined

  // Transform chart data using shared hook
  const { data: chartData, metrics: chartMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'sessions', label: __('Sessions', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when dates change (not timeframe-only)
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  // Show loading indicator on chart only when timeframe changes
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Build metrics from visitor_metrics response
  const visitorMetrics = useMemo(() => {
    const totals = visitorMetricsResponse?.totals

    const sessions = getTotalValue(totals?.sessions?.current) || 0
    const views = getTotalValue(totals?.views?.current) || 0
    const bounceRate = getTotalValue(totals?.bounce_rate?.current) || 0
    const avgSessionDuration = getTotalValue(totals?.avg_session_duration?.current) || 0
    const pagesPerSession = getTotalValue(totals?.pages_per_session?.current) || 0

    const prevSessions = getTotalValue(totals?.sessions?.previous) || 0
    const prevViews = getTotalValue(totals?.views?.previous) || 0
    const prevBounceRate = getTotalValue(totals?.bounce_rate?.previous) || 0
    const prevAvgSessionDuration = getTotalValue(totals?.avg_session_duration?.previous) || 0
    const prevPagesPerSession = getTotalValue(totals?.pages_per_session?.previous) || 0

    // Build all metrics with IDs for filtering
    const allMetrics: (MetricItem & { id: string })[] = [
      {
        id: 'sessions',
        label: __('Total Sessions', 'wp-statistics'),
        value: formatCompactNumber(sessions),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(sessions, prevSessions),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevSessions),
            }
          : {}),
      },
      {
        id: 'views',
        label: __('Total Page Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(views, prevViews),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevViews),
            }
          : {}),
      },
      {
        id: 'bounce-rate',
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...(isCompareEnabled
          ? {
              ...calcPercentage(bounceRate, prevBounceRate),
              comparisonDateLabel,
              previousValue: `${formatDecimal(prevBounceRate)}%`,
            }
          : {}),
      },
      {
        id: 'session-duration',
        label: __('Avg Session Duration', 'wp-statistics'),
        value: formatDuration(avgSessionDuration),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(avgSessionDuration, prevAvgSessionDuration),
              comparisonDateLabel,
              previousValue: formatDuration(prevAvgSessionDuration),
            }
          : {}),
      },
      {
        id: 'pages-per-session',
        label: __('Pages per Session', 'wp-statistics'),
        value: formatDecimal(pagesPerSession),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(pagesPerSession, prevPagesPerSession),
              comparisonDateLabel,
              previousValue: formatDecimal(prevPagesPerSession),
            }
          : {}),
      },
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [visitorMetricsResponse, calcPercentage, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  // Transform top pages for HorizontalBarList with deduplication by page_wp_id
  const topPagesItems = useMemo(() => {
    const rows = topPagesResponse?.data?.rows || []
    if (!rows.length) return []

    // Deduplicate by page_wp_id (WordPress post ID), summing views
    // This handles cases where same page is accessed via different URIs
    const pageMap = new Map<string, TopPageRow>()
    for (const row of rows) {
      // Use page_wp_id as key if available, otherwise fall back to page_uri
      const key = row.page_wp_id ? String(row.page_wp_id) : row.page_uri
      const existing = pageMap.get(key)
      if (existing) {
        // Sum views for duplicate pages
        existing.views = Number(existing.views) + Number(row.views)
        if (row.previous?.views) {
          existing.previous = existing.previous || { views: 0 }
          existing.previous.views = (existing.previous.views || 0) + (row.previous.views || 0)
        }
      } else {
        // Clone the row to avoid mutating original data
        pageMap.set(key, { ...row })
      }
    }

    // Convert back to array and sort by views (descending)
    const deduped = Array.from(pageMap.values()).sort((a, b) => Number(b.views) - Number(a.views))

    // Take top 5 after deduplication
    const top5 = deduped.slice(0, 5)
    const totalViews = top5.reduce((sum, row) => sum + (Number(row.views) || 0), 0)

    return transformToBarList<TopPageRow>(top5, {
      label: (item) => item.page_title || item.page_uri || __('Unknown', 'wp-statistics'),
      value: (item) => Number(item.views) || 0,
      previousValue: (item) => Number(item.previous?.views) || 0,
      total: totalViews,
      isCompareEnabled,
      comparisonDateLabel,
    })
  }, [topPagesResponse, isCompareEnabled, comparisonDateLabel])

  // Transform top referrers for HorizontalBarList - include Direct traffic
  const topReferrersItems = useMemo(() => {
    const rows = topReferrersResponse?.data?.rows || []
    const referredSessions = rows.reduce((sum, row) => sum + (Number(row.sessions) || 0), 0)

    // Get total sessions from metrics to calculate Direct traffic
    const totalSessions = Number(
      visitorMetricsResponse?.totals?.sessions?.current || 0
    )
    const directSessions = Math.max(0, totalSessions - referredSessions)

    // Add Direct traffic row if there are direct sessions
    const allRows: ReferrerRow[] = directSessions > 0
      ? [
          ...rows,
          {
            referrer_domain: '',
            referrer_name: __('Direct', 'wp-statistics'),
            referrer_channel: 'direct',
            sessions: directSessions,
          },
        ]
      : rows

    // Calculate total including Direct for percentage bars
    const totalForBars = referredSessions + directSessions

    return transformToBarList<ReferrerRow>(allRows, {
      label: (item) => item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics'),
      value: (item) => Number(item.sessions) || 0,
      previousValue: (item) => Number(item.previous?.sessions) || 0,
      total: totalForBars,
      isCompareEnabled,
      comparisonDateLabel,
    })
  }, [topReferrersResponse, visitorMetricsResponse, isCompareEnabled, comparisonDateLabel])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="px-4 py-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <BackButton defaultTo="/visitors" label={__('Back to Visitors', 'wp-statistics')} />
            <h1 className="text-2xl font-semibold text-neutral-800">
              {showSkeleton ? __('Loading...', 'wp-statistics') : __('Visitor Report', 'wp-statistics')}
            </h1>
          </div>
          <div className="flex items-center gap-3">
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
      </div>

      {/* Options Drawer */}
      <SingleVisitorOptionsDrawer
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
        resetToDefaults={options.resetToDefaults}
        sessionTableRef={sessionTableRef}
        onColumnVisibilityChange={handleSessionColumnVisibilityChange}
        onColumnOrderChange={handleSessionColumnOrderChange}
        onColumnReset={handleSessionColumnPreferencesReset}
      />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="single-visitor" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Profile Skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <div className="h-24" />
              </PanelSkeleton>
            </div>
            {/* Metrics Skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={5} columns={5} />
              </PanelSkeleton>
            </div>
            {/* Chart Skeleton */}
            <div className="col-span-12">
              <ChartSkeleton />
            </div>
            {/* Session History Skeleton */}
            <div className="col-span-12">
              <PanelSkeleton>
                <TableSkeleton rows={5} columns={6} />
              </PanelSkeleton>
            </div>
            {/* Top Pages / Referrers Skeletons */}
            <div className="col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Visitor Profile Card */}
            <div className="col-span-12">
              <VisitorProfileCard type={type} visitorInfo={visitorInfo} />
            </div>

            {/* Row 2: Metrics */}
            {isWidgetVisible('metrics') && visitorMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={visitorMetrics} columns="auto" />
                </Panel>
              </div>
            )}

            {/* Row 3: Activity Timeline Chart */}
            {isWidgetVisible('activity-timeline') && (
              <div className="col-span-12">
                <LineChart
                  className="h-full"
                  title={__('Activity Timeline', 'wp-statistics')}
                  data={chartData}
                  metrics={chartMetrics}
                  showPreviousPeriod={isCompareEnabled}
                  timeframe={timeframe}
                  onTimeframeChange={handleTimeframeChange}
                  loading={isChartRefetching}
                  compareDateTo={apiDateParams.previous_date_to}
                  dateTo={apiDateParams.date_to}
                />
              </div>
            )}

            {/* Row 4: Session History */}
            {isWidgetVisible('session-history') && (
              <div className="col-span-12">
                <SessionHistoryTable
                  sessions={sessionsResponse?.data?.rows || []}
                  tableRef={sessionTableRef}
                  initialColumnVisibility={initialSessionColumnVisibility ?? undefined}
                  columnOrder={sessionColumnOrder.length > 0 ? sessionColumnOrder : undefined}
                  onColumnVisibilityChange={handleSessionColumnVisibilityChange}
                  onColumnOrderChange={handleSessionColumnOrderChange}
                />
              </div>
            )}

            {/* Row 5: Top Pages and Referral Sources */}
            {isWidgetVisible('top-pages') && (
              <div className="col-span-6">
                <HorizontalBarList
                  title={__('Top Pages Visited', 'wp-statistics')}
                  items={topPagesItems}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Page', 'wp-statistics'),
                    right: __('Views', 'wp-statistics'),
                  }}
                />
              </div>
            )}
            {isWidgetVisible('top-referrers') && (
              <div className="col-span-6">
                <HorizontalBarList
                  title={__('Referral Sources', 'wp-statistics')}
                  items={topReferrersItems}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Source', 'wp-statistics'),
                    right: __('Sessions', 'wp-statistics'),
                  }}
                />
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}

/**
 * Visitor Profile Card - displays different content based on visitor type
 */
interface VisitorProfileCardProps {
  type: VisitorType
  visitorInfo: VisitorInfo | undefined
}

function VisitorProfileCard({ type, visitorInfo }: VisitorProfileCardProps) {
  const pluginUrl = WordPress.getInstance().getPluginUrl()

  if (!visitorInfo) {
    return (
      <Panel className="p-4">
        <div className="text-sm text-neutral-500">{__('Visitor information not available.', 'wp-statistics')}</div>
      </Panel>
    )
  }

  // Device info display (common to all types)
  const DeviceInfo = ({
    browser,
    browserVersion,
    os,
    deviceType,
  }: {
    browser: string | null
    browserVersion: string | null
    os: string | null
    deviceType: string | null
  }) => {
    const parts = [browser && browserVersion ? `${browser} ${browserVersion}` : browser, os, deviceType].filter(Boolean)
    if (parts.length === 0) return null
    return <span className="text-xs text-neutral-500">{parts.join(' · ')}</span>
  }

  // Location display (for user and IP types)
  const LocationInfo = ({
    countryCode,
    countryName,
    regionName,
    cityName,
  }: {
    countryCode: string | null
    countryName: string | null
    regionName?: string | null
    cityName?: string | null
  }) => {
    if (!countryName) return null
    const parts = [cityName, regionName, countryName].filter(Boolean)
    return (
      <div className="flex items-center gap-1.5">
        {countryCode && (
          <img
            src={`${pluginUrl}public/images/flags/${countryCode}.svg`}
            alt={countryName}
            className="w-4 h-4 object-contain"
          />
        )}
        <span className="text-xs text-neutral-500">{parts.join(', ')}</span>
      </div>
    )
  }

  if (type === 'user') {
    const info = visitorInfo as UserVisitorInfo
    const initials = (info.user_login || 'U').substring(0, 2).toUpperCase()

    return (
      <Panel className="p-4">
        <div className="flex items-start gap-4">
          <Avatar className="h-16 w-16">
            <AvatarFallback className="text-lg">{initials}</AvatarFallback>
          </Avatar>
          <div className="flex-1 min-w-0">
            <div className="flex items-center gap-2 mb-1">
              <h2 className="text-lg font-semibold text-neutral-800 truncate">{info.user_login}</h2>
              {info.total_sessions > 1 && (
                <Badge variant="secondary" className="text-[10px] font-normal px-1.5 py-0">
                  {__('Returning', 'wp-statistics')}
                </Badge>
              )}
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-neutral-500">
              {info.user_email && <span>{info.user_email}</span>}
              {info.user_role && (
                <Badge variant="outline" className="text-[10px] font-normal capitalize px-1.5 py-0">
                  {info.user_role}
                </Badge>
              )}
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
              <LocationInfo
                countryCode={info.country_code}
                countryName={info.country_name}
                regionName={info.region_name}
                cityName={info.city_name}
              />
              <DeviceInfo
                browser={info.browser_name}
                browserVersion={info.browser_version}
                os={info.os_name}
                deviceType={info.device_type_name}
              />
            </div>
          </div>
        </div>
      </Panel>
    )
  }

  if (type === 'ip') {
    const info = visitorInfo as IpVisitorInfo

    return (
      <Panel className="p-4">
        <div className="flex items-start gap-4">
          <div className="h-16 w-16 rounded-full bg-neutral-100 flex items-center justify-center shrink-0">
            {info.country_code ? (
              <img
                src={`${pluginUrl}public/images/flags/${info.country_code}.svg`}
                alt={info.country_name || ''}
                className="w-8 h-8 object-contain"
              />
            ) : (
              <Globe className="h-8 w-8 text-neutral-400" />
            )}
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-center gap-2 mb-1">
              <h2 className="text-lg font-semibold text-neutral-800 font-mono">{info.ip_address}</h2>
              {info.total_sessions > 1 && (
                <Badge variant="secondary" className="text-[10px] font-normal px-1.5 py-0">
                  {__('Returning', 'wp-statistics')}
                </Badge>
              )}
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
              <LocationInfo
                countryCode={info.country_code}
                countryName={info.country_name}
                regionName={info.region_name}
                cityName={info.city_name}
              />
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
              <DeviceInfo
                browser={info.browser_name}
                browserVersion={info.browser_version}
                os={info.os_name}
                deviceType={info.device_type_name}
              />
            </div>
          </div>
        </div>
      </Panel>
    )
  }

  // Hash type
  const info = visitorInfo as HashVisitorInfo
  const shortHash = (info.visitor_hash || '').replace(/^#hash#/i, '').substring(0, 8) || '------'

  return (
    <Panel className="p-4">
      <div className="flex items-start gap-4">
        <div className="h-16 w-16 rounded-full bg-neutral-100 flex items-center justify-center shrink-0">
          <Hash className="h-8 w-8 text-neutral-400" />
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <h2 className="text-lg font-semibold text-neutral-800">
              <span className="text-neutral-400">#</span>
              <span className="font-mono">{shortHash}</span>
            </h2>
            {info.total_sessions > 1 && (
              <Badge variant="secondary" className="text-[10px] font-normal px-1.5 py-0">
                {__('Returning', 'wp-statistics')}
              </Badge>
            )}
          </div>
          <div className="text-xs text-neutral-500 mb-2">{__('Anonymous Visitor', 'wp-statistics')}</div>
          <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
            <DeviceInfo
              browser={info.browser_name}
              browserVersion={info.browser_version}
              os={info.os_name}
              deviceType={info.device_type_name}
            />
          </div>
        </div>
      </div>
    </Panel>
  )
}

/**
 * SubRow component for expandable session rows - shows page views within session
 */
function SessionPageViewsSubRow({ row }: { row: Row<SessionRow> }) {
  const sessionId = row.original.session_id

  const { data, isLoading } = useQuery({
    ...getSessionPageViewsQueryOptions({ sessionId }),
    placeholderData: keepPreviousData,
  })

  // Helper to format timestamp without timezone shift
  const formatTimestamp = (timestamp: string | null) => {
    if (!timestamp) return '-'
    const [datePart, timePart] = timestamp.split(' ')
    if (!timePart) return datePart
    const [hours, minutes] = timePart.split(':').map(Number)
    const hour12 = hours % 12 || 12
    const ampm = hours < 12 ? 'AM' : 'PM'
    return `${hour12}:${minutes.toString().padStart(2, '0')} ${ampm}`
  }

  if (isLoading) {
    return <div className="py-4 pl-14 text-sm text-neutral-500">{__('Loading...', 'wp-statistics')}</div>
  }

  const pageViews = data?.data?.data?.rows || []

  if (!pageViews.length) {
    return <div className="py-4 pl-14 text-sm text-neutral-500">{__('No page views recorded', 'wp-statistics')}</div>
  }

  return (
    <div className="border-t border-neutral-200">
      <table className="w-full">
        <thead>
          <tr className="bg-neutral-100/60">
            <td className="pl-14 py-1.5 text-xs font-medium text-neutral-500">{__('Page', 'wp-statistics')}</td>
            <td className="py-1.5 text-xs font-medium text-neutral-500 text-right">{__('Time on Page', 'wp-statistics')}</td>
            <td className="py-1.5 pr-4 text-xs font-medium text-neutral-500 text-right">{__('Time', 'wp-statistics')}</td>
          </tr>
        </thead>
        <tbody>
          {pageViews.map((view: PageViewRow, idx: number) => (
            <tr key={idx} className="border-t border-neutral-100 bg-neutral-50 hover:bg-neutral-100/50">
              <td className="pl-14 py-1.5">
                <span className="text-xs font-medium text-neutral-700 truncate block max-w-md">
                  {view.page_title || view.page_uri}
                </span>
              </td>
              <td className="py-1.5 text-right">
                <DurationCell seconds={view.time_on_page || 0} />
              </td>
              <td className="py-1.5 pr-4 text-right">
                <span className="text-xs text-neutral-500">{formatTimestamp(view.timestamp)}</span>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

/**
 * Session History Table - displays individual sessions with entry/exit pages
 */
interface SessionHistoryTableProps {
  sessions: SessionRow[]
  tableRef?: React.MutableRefObject<Table<SessionRow> | null>
  initialColumnVisibility?: VisibilityState
  columnOrder?: string[]
  onColumnVisibilityChange?: (visibility: VisibilityState) => void
  onColumnOrderChange?: (order: string[]) => void
}

function SessionHistoryTable({
  sessions,
  tableRef,
  initialColumnVisibility,
  columnOrder,
  onColumnVisibilityChange,
  onColumnOrderChange,
}: SessionHistoryTableProps) {
  // Define columns for session history table
  const sessionColumns = useMemo<ColumnDef<SessionRow>[]>(
    () => [
      // Expand toggle column
      {
        id: 'expand',
        size: 40,
        header: () => null,
        cell: ({ row }) => (
          row.getCanExpand() ? (
            <button
              onClick={(e) => { e.stopPropagation(); row.toggleExpanded() }}
              className="p-1 hover:bg-neutral-100 rounded"
            >
              {row.getIsExpanded() ? (
                <ChevronDown className="h-4 w-4 text-neutral-500" />
              ) : (
                <ChevronRight className="h-4 w-4 text-neutral-500" />
              )}
            </button>
          ) : <span className="w-6" />
        ),
        enableSorting: false,
      },
      {
        accessorKey: 'session_start',
        header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
        cell: ({ row }) => (
          <span className="text-xs font-medium text-neutral-700">
            {row.original.session_start_formatted || row.original.session_start || '-'}
          </span>
        ),
        meta: {
          title: __('Date/Time', 'wp-statistics'),
          priority: 'primary',
          mobileLabel: __('Time', 'wp-statistics'),
          cardPosition: 'header',
        },
      },
      {
        accessorKey: 'session_duration',
        header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
        size: COLUMN_SIZES.duration,
        cell: ({ row }) => <DurationCell seconds={row.original.session_duration || 0} />,
        meta: {
          title: __('Duration', 'wp-statistics'),
          priority: 'primary',
          cardPosition: 'body',
        },
      },
      {
        accessorKey: 'page_count',
        header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
        size: COLUMN_SIZES.views,
        cell: ({ row }) => <NumericCell value={row.original.page_count || 0} />,
        meta: {
          title: __('Pages', 'wp-statistics'),
          priority: 'primary',
          cardPosition: 'body',
        },
      },
      {
        accessorKey: 'entry_page',
        header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
        cell: ({ row }) => (
          <EntryPageCell
            data={{
              title: row.original.entry_page_title || row.original.entry_page || '',
              url: row.original.entry_page || '',
            }}
          />
        ),
        enableSorting: false,
        meta: {
          title: __('Entry Page', 'wp-statistics'),
          priority: 'primary',
          cardPosition: 'body',
        },
      },
      {
        accessorKey: 'exit_page',
        header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
        cell: ({ row }) => (
          <PageCell
            data={{
              title: row.original.exit_page_title || row.original.exit_page || '',
              url: row.original.exit_page || '',
            }}
          />
        ),
        enableSorting: false,
        meta: {
          title: __('Exit Page', 'wp-statistics'),
          priority: 'primary',
          cardPosition: 'body',
        },
      },
      {
        accessorKey: 'referrer_domain',
        header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
        cell: ({ row }) => (
          <ReferrerCell
            data={{
              domain: row.original.referrer_domain || undefined,
              category: formatReferrerChannel(row.original.referrer_channel),
            }}
          />
        ),
        enableSorting: false,
        meta: {
          title: __('Referrer', 'wp-statistics'),
          priority: 'secondary',
          cardPosition: 'footer',
        },
      },
    ],
    []
  )

  return (
    <DataTable
      title={__('Session History', 'wp-statistics')}
      columns={sessionColumns}
      data={sessions}
      emptyMessage={__('No sessions found for this visitor.', 'wp-statistics')}
      showColumnManagement={false}
      getRowCanExpand={() => true}
      renderSubComponent={({ row }) => <SessionPageViewsSubRow row={row} />}
      stickyHeader={true}
      tableRef={tableRef}
      initialColumnVisibility={initialColumnVisibility}
      columnOrder={columnOrder}
      onColumnVisibilityChange={onColumnVisibilityChange}
      onColumnOrderChange={onColumnOrderChange}
    />
  )
}
