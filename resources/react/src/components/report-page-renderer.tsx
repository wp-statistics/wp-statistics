/**
 * Report Page Renderer
 *
 * A generic component that renders premium report pages with consistent UI:
 * - Header with title, date picker, filter button, options drawer
 * - DataTable with pagination, sorting, column management
 * - Loading/error states
 *
 * Premium modules provide a config object, core renders the full UI.
 * Supports three levels of customization:
 * - Level 1: Config-based (simple table reports)
 * - Level 2: Config + Slots (moderate customization with custom components)
 * - Level 3: Full custom (use registerPage instead)
 */

import type { QueryKey } from '@tanstack/react-query'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import type { ColumnDef, Table } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { type ReactNode, useCallback, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import type { LockedFilter } from '@/components/custom/filter-panel'
import { LineChart } from '@/components/custom/line-chart'
import {
  OptionsDrawerTrigger,
  type PageFilterConfig,
  TableOptionsDrawer,
  useTableOptions,
} from '@/components/custom/options-drawer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { ChartSkeleton, PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import type { ColumnConfig } from '@/lib/column-utils'
import { ExpandableSubRow } from '@/lib/expandable-sub-row'
import { extractBatchItem, extractMeta, extractRows, type Timeframe } from '@/lib/response-helpers'
import { WordPress } from '@/lib/wordpress'
import type { ChartApiResponse } from '@/types/chart'

/**
 * Query options factory function type
 * Premium modules provide this to create the query configuration
 */
export type QueryOptionsFactory<TParams = unknown, TResponse = unknown> = (
  params: TParams
) => {
  queryKey: QueryKey
  queryFn: () => Promise<TResponse>
}

/**
 * Column factory function type
 * Creates columns with optional comparison support
 */
export type ColumnFactory<TData> = (options: {
  comparisonLabel?: string
  comparisonColumns?: string[]
}) => ColumnDef<TData>[]

/**
 * Data transformer function type
 * Transforms API response rows to table data format
 */
export type DataTransformer<TRecord, TData> = (record: TRecord) => TData

/**
 * Slot render props passed to custom slot components
 */
export interface SlotRenderProps<TData = unknown> {
  data: TData[]
  isLoading: boolean
  isFetching: boolean
  rawResponse: unknown
}

/**
 * Report configuration for config-based registration
 */
export interface ReportConfig<TData = unknown, TRecord = unknown> {
  /** Report title displayed in header */
  title: string
  /** Unique context identifier for preferences storage */
  context: string
  /** Filter group to use (e.g., 'sessions', 'views') */
  filterGroup: string
  /** Optional route name for notice container */
  routeName?: string
  /** Query options factory - creates the query configuration */
  queryOptions: QueryOptionsFactory<{
    page: number
    per_page: number
    order_by: string
    order: 'asc' | 'desc'
    date_from: string
    date_to: string
    previous_date_from?: string
    previous_date_to?: string
    filters: unknown[]
    apiFilters?: Record<string, unknown>
  }>
  /** Column factory - creates column definitions */
  columns: ColumnFactory<TData>
  /** Data transformer - transforms API rows to table data */
  transformData: DataTransformer<TRecord, TData>
  /** Default sort configuration */
  defaultSort?: { id: string; desc: boolean }
  /** Rows per page */
  perPage?: number
  /** Columns hidden by default */
  defaultHiddenColumns?: string[]
  /** Columns that support comparison display */
  comparableColumns?: string[]
  /** Default columns showing comparison */
  defaultComparisonColumns?: string[]
  /** Column config for API optimization */
  columnConfig?: ColumnConfig
  /** Default API columns */
  defaultApiColumns?: string[]
  /** Empty state message */
  emptyStateMessage?: string
  /** Custom filters (subset of filter group) */
  customFilters?: string[]
  /** Hide the filter button entirely */
  hideFilters?: boolean
  /** Locked filters displayed as read-only rows in filter panel */
  lockedFilters?: LockedFilter[]
  /** Hardcoded filters always merged into API requests */
  hardcodedFilters?: Array<{ id: string; label: string; operator: string; rawOperator: string; value: string; rawValue: string }>
  /** Page-specific filter dropdowns (shown in Options drawer on mobile) */
  pageFilters?: PageFilterConfig[]
  /** Whether the data query is enabled (default: true). When false, table shows empty state without fetching. */
  enabled?: boolean
  /** Chart config for built-in chart-above-table rendering (with metrics + timeframe support) */
  chart?: PhpChartConfig
  /** Header filter dropdown config — consumed by PhpReportRoute, not by this renderer */
  headerFilter?: PhpHeaderFilter
  /** Expandable rows config for sub-row drill-down (e.g., browser → versions) */
  expandableRows?: PhpExpandableRowsConfig

  // Level 2: Slot components for customization
  /** Component rendered before the table */
  beforeTable?: (props: SlotRenderProps<TData>) => ReactNode
  /** Component rendered after the table */
  afterTable?: (props: SlotRenderProps<TData>) => ReactNode
  /** Extra components in header (next to date picker) */
  headerActions?: () => ReactNode
}

interface ReportPageRendererProps<TData = unknown, TRecord = unknown> {
  config: ReportConfig<TData, TRecord>
  /** Additional API filters merged into query requests */
  apiFilters?: Record<string, unknown>
  /** Query overrides merged into the data source query (e.g., group_by override from UTM type selector) */
  queryOverrides?: Record<string, unknown>
}

/**
 * Generic report page renderer component
 */
export function ReportPageRenderer<TData, TRecord>({
  config,
  apiFilters,
  queryOverrides,
}: ReportPageRendererProps<TData, TRecord>) {
  const {
    title,
    context,
    filterGroup,
    routeName,
    queryOptions,
    columns: columnFactory,
    transformData,
    defaultSort = { id: 'views', desc: true },
    perPage = 20,
    defaultHiddenColumns = [],
    comparableColumns = [],
    defaultComparisonColumns = [],
    columnConfig,
    defaultApiColumns = [],
    emptyStateMessage = __('No data found for the selected period', 'wp-statistics'),
    customFilters,
    hideFilters: hideFiltersConfig,
    lockedFilters,
    hardcodedFilters,
    pageFilters,
    enabled: queryEnabled = true,
    chart: chartConfig,
    expandableRows,
    beforeTable,
    afterTable,
    headerActions,
  } = config

  // Built-in chart: render when chart config has metrics and no custom beforeTable override
  const hasBuiltInChart = !!chartConfig?.metrics && !beforeTable

  const [timeframe, setTimeframe] = useState<Timeframe>('daily')

  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    page,
    setPage,
    setDateRange,
    applyFilters: handleApplyFilters,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [defaultSort],
    onPageReset: () => setPage(1),
  })

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<TData> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  const wp = WordPress.getInstance()

  // Filter fields from the filter group
  const filterFields = useMemo<FilterField[]>(() => {
    const allFields = wp.getFilterFieldsByGroup(filterGroup) as FilterField[]
    if (customFilters && customFilters.length > 0) {
      return allFields.filter((field) => customFilters.includes(field.name))
    }
    return allFields
  }, [wp, filterGroup, customFilters])

  // Base columns for preferences hook
  const baseColumns = useMemo(
    () => columnFactory({ comparisonLabel }),
    [columnFactory, comparisonLabel]
  )

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string; comparisonMode?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period, values.comparisonMode as 'none' | 'previous_period' | 'same_period_last_year' | undefined)
    },
    [setDateRange]
  )

  // Merge hardcoded filters with user-applied filters
  const mergedFilters = useMemo(() => {
    const base = appliedFilters || []
    if (!hardcodedFilters || hardcodedFilters.length === 0) return base
    return [...hardcodedFilters, ...base]
  }, [hardcodedFilters, appliedFilters])

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...queryOptions({
      page,
      per_page: perPage,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: mergedFilters,
      ...(apiFilters && { apiFilters }),
      ...(hasBuiltInChart && { timeframe }),
      ...(queryOverrides && { queryOverrides }),
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized && queryEnabled,
  })

  // Use the preferences hook for column management (only if columnConfig provided)
  const hasColumnConfig = !!columnConfig && defaultApiColumns.length > 0
  const {
    defaultColumnOrder,
    columnOrder,
    initialColumnVisibility,
    comparisonColumns,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleComparisonColumnsChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context,
    columns: baseColumns,
    defaultHiddenColumns,
    defaultApiColumns: hasColumnConfig ? defaultApiColumns : [],
    columnConfig: columnConfig || { baseColumns: [], columnDependencies: {}, context },
    sorting,
    defaultSortColumn: defaultSort.id,
    preferencesFromApi: (response as { data?: { meta?: { preferences?: { columns?: string[] } } } })?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response,
    defaultComparisonColumns,
    comparisonColumnsFromApi: (response as { data?: { meta?: { preferences?: { comparison_columns?: string[] } } } })?.data?.meta?.preferences?.comparison_columns,
  })

  // Options drawer with column management
  const options = useTableOptions({
    filterGroup,
    table: tableRef.current,
    lockedFilters,
    pageFilters,
    initialColumnOrder: defaultColumnOrder,
    columnOrder,
    defaultHiddenColumns,
    initialColumnVisibility,
    comparableColumns,
    comparisonColumns,
    defaultComparisonColumns,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => columnFactory({ comparisonLabel, comparisonColumns }),
    [columnFactory, comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface
  const tableData = useMemo(() => {
    return extractRows(response).map((row) => transformData(row as TRecord))
  }, [response, transformData])

  const meta = extractMeta(response)
  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  const showSkeleton = isLoading && !response
  const isCompareEnabled = !!(apiDateParams.previous_date_from && apiDateParams.previous_date_to)

  // Built-in chart data extraction (only when chart config with metrics exists)
  const chartResponse = hasBuiltInChart
    ? extractBatchItem<ChartApiResponse>(response, chartConfig!.queryId)
    : undefined

  const chartMetricConfigs = useMemo(
    () => chartConfig?.metrics?.map((m) => ({ key: m.key, label: m.label, color: m.color })) ?? [],
    [chartConfig?.metrics]
  )

  const { data: chartData, metrics: chartMetrics } = useChartData(
    hasBuiltInChart ? chartResponse : undefined,
    {
      metrics: chartMetricConfigs,
      showPreviousValues: isCompareEnabled,
      preserveNull: true,
    }
  )

  // Slot render props
  const slotProps: SlotRenderProps<TData> = {
    data: tableData,
    isLoading,
    isFetching,
    rawResponse: response,
  }

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">{title}</h1>
        <div className="flex items-center gap-3" data-pdf-hide>
          <div className="hidden lg:flex">
            {!hideFiltersConfig && filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={appliedFilters || []}
                onApplyFilters={handleApplyFilters}
                filterGroup={filterGroup}
                lockedFilters={lockedFilters}
              />
            )}
          </div>
          <DateRangePicker
            initialDateFrom={dateFrom}
            initialDateTo={dateTo}
            initialCompareFrom={compareDateFrom}
            initialCompareTo={compareDateTo}
            initialPeriod={period}
            onUpdate={handleDateRangeUpdate}
            showCompare={true}
            align="end"
          />
          {headerActions && <div className="hidden lg:flex">{headerActions()}</div>}
          <OptionsDrawerTrigger {...options.triggerProps} />
        </div>
      </div>

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer
        config={{
          filterGroup,
          table: tableRef.current,
          pageFilters,
          initialColumnOrder: columnOrder,
          defaultHiddenColumns,
          comparableColumns,
          comparisonColumns,
          defaultComparisonColumns,
          onColumnVisibilityChange: handleColumnVisibilityChange,
          onColumnOrderChange: handleColumnOrderChange,
          onComparisonColumnsChange: handleComparisonColumnsChange,
          onReset: handleColumnPreferencesReset,
        }}
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
      />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute={routeName || context} />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load data', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{(error as Error)?.message}</p>
          </div>
        ) : showSkeleton ? (
          <div className="space-y-3">
            {(hasBuiltInChart || beforeTable) && (
              <PanelSkeleton titleWidth="w-32">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            )}
            <PanelSkeleton titleWidth="w-24">
              <TableSkeleton rows={10} columns={4} />
            </PanelSkeleton>
          </div>
        ) : (
          <div className="space-y-3">
            {hasBuiltInChart && (
              <LineChart
                title={chartConfig!.title || __('Traffic Trends', 'wp-statistics')}
                data={chartData}
                metrics={chartMetrics}
                showPreviousPeriod={isCompareEnabled}
                timeframe={timeframe}
                onTimeframeChange={setTimeframe}
                loading={isFetching && !isLoading}
                borderless
                dateTo={apiDateParams.date_to}
                compareDateTo={apiDateParams.previous_date_to}
              />
            )}
            {beforeTable && beforeTable(slotProps)}
            <DataTable
              columns={columns as ColumnDef<TData>[]}
              data={tableData}
              sorting={sorting}
              onSortingChange={handleSortingChange}
              manualSorting={true}
              manualPagination={true}
              pageCount={totalPages}
              page={page}
              onPageChange={handlePageChange}
              totalRows={totalRows}
              rowLimit={perPage}
              showPagination={true}
              isFetching={isFetching}
              hiddenColumns={defaultHiddenColumns}
              initialColumnVisibility={initialColumnVisibility}
              columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
              onColumnVisibilityChange={handleColumnVisibilityChange}
              onColumnOrderChange={handleColumnOrderChange}
              onColumnPreferencesReset={handleColumnPreferencesReset}
              comparableColumns={comparableColumns}
              comparisonColumns={comparisonColumns}
              defaultComparisonColumns={defaultComparisonColumns}
              onComparisonColumnsChange={handleComparisonColumnsChange}
              emptyStateMessage={emptyStateMessage}
              stickyHeader={true}
              borderless
              tableRef={tableRef}
              {...(expandableRows && {
                getRowCanExpand: () => true,
                renderSubComponent: ({ row }: { row: import('@tanstack/react-table').Row<TData> }) => (
                  <ExpandableSubRow
                    row={row as unknown as import('@tanstack/react-table').Row<Record<string, unknown>>}
                    config={expandableRows}
                    apiDateParams={apiDateParams}
                  />
                ),
              })}
            />
          </div>
        )}

        {/* After table slot */}
        {afterTable && !showSkeleton && !isError && afterTable(slotProps)}
      </div>
    </div>
  )
}
