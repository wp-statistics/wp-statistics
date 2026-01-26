import type { Table } from '@tanstack/react-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo, useRef } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import type { LockedFilter } from '@/components/custom/filter-panel'
import { TableOptionsDrawer, useTableOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  createVisitorsColumns,
  transformVisitorData,
  type Visitor,
  VISITORS_COLUMN_CONFIG,
  VISITORS_DEFAULT_API_COLUMNS,
  VISITORS_DEFAULT_HIDDEN_COLUMNS,
} from '@/components/data-table-columns/visitors-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { WordPress } from '@/lib/wordpress'
import { getVisitorsQueryOptions, type VisitorRecord } from '@/services/visitor-insight/get-visitors'

const PER_PAGE = 25

/**
 * Context identifier for separate column preferences from main Visitors page
 */
const REFERRED_VISITORS_CONTEXT = 'referred_visitors'

/**
 * Column configuration for referred visitors (uses same config as visitors)
 */
const REFERRED_VISITORS_COLUMN_CONFIG = {
  ...VISITORS_COLUMN_CONFIG,
  context: REFERRED_VISITORS_CONTEXT,
}

export const Route = createLazyFileRoute('/(referrals)/referred-visitors')({
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
    defaultSort: [{ id: 'lastVisit', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<Visitor> | null>(null)

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const columns = useMemo(
    () =>
      createVisitorsColumns({
        pluginUrl,
        trackLoggedInEnabled: wp.isTrackLoggedInEnabled(),
        hashEnabled: wp.isHashEnabled(),
      }),
    [pluginUrl, wp]
  )

  // Hardcoded filter to exclude direct traffic (show only referred visitors)
  const referredFilter = useMemo(
    () => ({
      id: 'referrer_channel-hardcoded',
      label: __('Traffic Channel', 'wp-statistics'),
      operator: __('is not', 'wp-statistics'),
      rawOperator: 'is_not',
      value: __('Direct', 'wp-statistics'),
      rawValue: 'direct',
    }),
    []
  )

  // Locked filter for display in Options drawer (read-only)
  const lockedFilters: LockedFilter[] = useMemo(
    () => [
      {
        id: 'referrer_channel-locked',
        label: __('Traffic Channel', 'wp-statistics'),
        operator: __('is not', 'wp-statistics'),
        value: __('Direct', 'wp-statistics'),
      },
    ],
    []
  )

  // Merge hardcoded filter with user-applied filters
  const mergedFilters = useMemo(() => {
    return [referredFilter, ...(appliedFilters || [])]
  }, [referredFilter, appliedFilters])

  // Fetch data from API (initial fetch uses default/cached columns)
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getVisitorsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: mergedFilters,
      context: REFERRED_VISITORS_CONTEXT,
      columns: VISITORS_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Use the preferences hook for column management
  const {
    columnOrder,
    initialColumnVisibility,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: REFERRED_VISITORS_CONTEXT,
    columns,
    defaultHiddenColumns: VISITORS_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: VISITORS_DEFAULT_API_COLUMNS,
    columnConfig: REFERRED_VISITORS_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'lastVisit',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
  })

  // Options drawer with column management - config is passed once and returned for drawer
  const options = useTableOptions({
    filterGroup: 'visitors',
    table: tableRef.current,
    lockedFilters,
    defaultHiddenColumns: VISITORS_DEFAULT_HIDDEN_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onReset: handleColumnPreferencesReset,
  })

  // Transform API data to component interface
  const tableData = useMemo(() => {
    return extractRows<VisitorRecord>(response).map(transformVisitorData)
  }, [response])

  const meta = extractMeta(response)
  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Referred Visitors', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
        lockedFilters={lockedFilters}
      />

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="referred-visitors" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load referred visitors', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={8} />
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
            hiddenColumns={VISITORS_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            emptyStateMessage={__('No referred visitors found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
