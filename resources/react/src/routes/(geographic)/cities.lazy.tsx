import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { Table } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useMemo, useRef } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import { TableOptionsDrawer, useTableOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  CITIES_COLUMN_CONFIG,
  CITIES_COMPARABLE_COLUMNS,
  CITIES_CONTEXT,
  CITIES_DEFAULT_API_COLUMNS,
  CITIES_DEFAULT_COMPARISON_COLUMNS,
  CITIES_DEFAULT_HIDDEN_COLUMNS,
  type CityData,
  createCitiesColumns,
  transformCityData,
} from '@/components/data-table-columns/cities-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { WordPress } from '@/lib/wordpress'
import { extractCitiesData, getCitiesQueryOptions } from '@/services/geographic/get-cities'

const PER_PAGE = 25

export const Route = createLazyFileRoute('/(geographic)/cities')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    filters: appliedFilters,
    page,
    setPage,
    handlePageChange,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'visitors', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<CityData> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  // Get plugin URL for flag images
  const pluginUrl = WordPress.getInstance().getPluginUrl()

  // Base columns for preferences hook (stable definition for column IDs)
  const baseColumns = useMemo(
    () => createCitiesColumns({ pluginUrl, comparisonLabel }),
    [pluginUrl, comparisonLabel]
  )

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getCitiesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters,
      context: CITIES_CONTEXT,
      columns: CITIES_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Extract data using the helper function (handles batch response format)
  const { rows, meta } = useMemo(() => extractCitiesData(response), [response])

  // Get preferences from the batch response
  const preferencesFromApi = response?.data?.items?.cities?.meta?.preferences?.columns
  const comparisonColumnsFromApi = response?.data?.items?.cities?.meta?.preferences?.comparison_columns

  // Use the preferences hook for column management
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
    context: CITIES_CONTEXT,
    columns: baseColumns,
    defaultHiddenColumns: CITIES_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: CITIES_DEFAULT_API_COLUMNS,
    columnConfig: CITIES_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'visitors',
    preferencesFromApi,
    hasApiResponse: !!response?.data,
    defaultComparisonColumns: CITIES_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi,
  })

  // Options drawer with column management
  const options = useTableOptions({
    filterGroup: 'visitors',
    table: tableRef.current,
    initialColumnOrder: defaultColumnOrder,
    columnOrder,
    defaultHiddenColumns: CITIES_DEFAULT_HIDDEN_COLUMNS,
    initialColumnVisibility,
    comparableColumns: CITIES_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: CITIES_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => createCitiesColumns({ pluginUrl, comparisonLabel, comparisonColumns }),
    [pluginUrl, comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface
  const tableData = useMemo(() => {
    return rows.map((record) => transformCityData(record))
  }, [rows])

  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Cities', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
      />

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="cities" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load cities', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={5} />
          </PanelSkeleton>
        ) : (
          <DataTable
            columns={columns}
            data={tableData}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={totalRows}
            rowLimit={PER_PAGE}
            showPagination={true}
            isFetching={isFetching}
            hiddenColumns={CITIES_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            comparableColumns={CITIES_COMPARABLE_COLUMNS}
            comparisonColumns={comparisonColumns}
            defaultComparisonColumns={CITIES_DEFAULT_COMPARISON_COLUMNS}
            onComparisonColumnsChange={handleComparisonColumnsChange}
            emptyStateMessage={__('No cities found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
