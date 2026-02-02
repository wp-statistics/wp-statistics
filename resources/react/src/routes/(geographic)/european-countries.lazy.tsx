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
  COUNTRIES_COLUMN_CONFIG,
  COUNTRIES_COMPARABLE_COLUMNS,
  COUNTRIES_DEFAULT_API_COLUMNS,
  COUNTRIES_DEFAULT_COMPARISON_COLUMNS,
  COUNTRIES_DEFAULT_HIDDEN_COLUMNS,
  type CountryData,
  EUROPEAN_COUNTRIES_CONTEXT,
  createCountriesColumns,
  transformCountryData,
} from '@/components/data-table-columns/countries-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { WordPress } from '@/lib/wordpress'
import { extractCountriesData, getCountriesQueryOptions } from '@/services/geographic/get-countries'

const PER_PAGE = 25

/** Filter to show only European countries */
const EUROPE_CONTINENT_FILTER = [{ key: 'continent', operator: 'is', value: 'EU' }]

export const Route = createLazyFileRoute('/(geographic)/european-countries')({
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
  const tableRef = useRef<Table<CountryData> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  // Get plugin URL for flag images
  const pluginUrl = WordPress.getInstance().getPluginUrl()

  // Base columns for preferences hook (stable definition for column IDs)
  const baseColumns = useMemo(
    () => createCountriesColumns({ pluginUrl, comparisonLabel, fromPath: '/european-countries' }),
    [pluginUrl, comparisonLabel]
  )

  // Fetch data from API with European countries filter
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getCountriesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters,
      context: EUROPEAN_COUNTRIES_CONTEXT,
      columns: COUNTRIES_DEFAULT_API_COLUMNS,
      queryFilters: EUROPE_CONTINENT_FILTER,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Extract data using the helper function (handles batch response format)
  const { rows, totals, meta } = useMemo(() => extractCountriesData(response), [response])

  // Get preferences from the batch response
  const preferencesFromApi = response?.data?.items?.countries?.meta?.preferences?.columns
  const comparisonColumnsFromApi = response?.data?.items?.countries?.meta?.preferences?.comparison_columns

  // Use the preferences hook for column management
  const {
    columnOrder,
    initialColumnVisibility,
    comparisonColumns,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleComparisonColumnsChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: EUROPEAN_COUNTRIES_CONTEXT,
    columns: baseColumns,
    defaultHiddenColumns: COUNTRIES_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: COUNTRIES_DEFAULT_API_COLUMNS,
    columnConfig: COUNTRIES_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'visitors',
    preferencesFromApi,
    hasApiResponse: !!response?.data,
    defaultComparisonColumns: COUNTRIES_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi,
  })

  // Options drawer with column management
  const options = useTableOptions({
    filterGroup: 'visitors',
    table: tableRef.current,
    defaultHiddenColumns: COUNTRIES_DEFAULT_HIDDEN_COLUMNS,
    comparableColumns: COUNTRIES_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: COUNTRIES_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => createCountriesColumns({ pluginUrl, comparisonLabel, comparisonColumns, fromPath: '/european-countries' }),
    [pluginUrl, comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface with totals for % of Total
  const totalVisitors = totals.visitors
  const tableData = useMemo(() => {
    return rows.map((record) => transformCountryData(record, totalVisitors))
  }, [rows, totalVisitors])

  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('European Countries', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
      />

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="european-countries" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load European countries', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={4} />
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
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            hiddenColumns={COUNTRIES_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            comparableColumns={COUNTRIES_COMPARABLE_COLUMNS}
            comparisonColumns={comparisonColumns}
            defaultComparisonColumns={COUNTRIES_DEFAULT_COMPARISON_COLUMNS}
            onComparisonColumnsChange={handleComparisonColumnsChange}
            emptyStateMessage={__('No European countries found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
