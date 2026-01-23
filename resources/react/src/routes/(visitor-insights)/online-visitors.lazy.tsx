import type { Table } from '@tanstack/react-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import {
  OptionsDrawerTrigger,
  TableOptionsDrawer,
  useTableOptions,
} from '@/components/custom/options-drawer'
import {
  createOnlineVisitorsColumns,
  ONLINE_VISITORS_COLUMN_CONFIG,
  ONLINE_VISITORS_CONTEXT,
  ONLINE_VISITORS_DEFAULT_API_COLUMNS,
  ONLINE_VISITORS_DEFAULT_HIDDEN_COLUMNS,
  transformOnlineVisitorData,
  type OnlineVisitor,
} from '@/components/data-table-columns/online-visitors-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { WordPress } from '@/lib/wordpress'
import { getOnlineVisitorsQueryOptions } from '@/services/visitor-insight/get-online-visitors'

export const Route = createLazyFileRoute('/(visitor-insights)/online-visitors')({
  component: RouteComponent,
})

const PER_PAGE = 50

function RouteComponent() {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const columns = useMemo(
    () =>
      createOnlineVisitorsColumns({
        pluginUrl,
        trackLoggedInEnabled: wp.isTrackLoggedInEnabled(),
        hashEnabled: wp.isHashEnabled(),
      }),
    [pluginUrl, wp]
  )

  const [page, setPage] = useState(1)

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<OnlineVisitor> | null>(null)

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'lastVisit', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isError,
    error,
    isFetching,
  } = useQuery({
    ...getOnlineVisitorsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      context: ONLINE_VISITORS_CONTEXT,
      columns: ONLINE_VISITORS_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
  })

  // Use the preferences hook for column management
  const {
    columnOrder,
    initialColumnVisibility,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: ONLINE_VISITORS_CONTEXT,
    columns,
    defaultHiddenColumns: ONLINE_VISITORS_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: ONLINE_VISITORS_DEFAULT_API_COLUMNS,
    columnConfig: ONLINE_VISITORS_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'lastVisit',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
  })

  // Options drawer config
  const tableOptionsConfig = {
    filterGroup: 'visitors',
    table: tableRef.current,
    hideFilters: true,
    defaultHiddenColumns: ONLINE_VISITORS_DEFAULT_HIDDEN_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onReset: handleColumnPreferencesReset,
  }

  // Options drawer with column management (no filters or date for this page)
  const options = useTableOptions(tableOptionsConfig)

  // Transform API data to component format
  const visitors = response?.data?.data?.rows?.map(transformOnlineVisitorData) || []
  const total = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(total / PER_PAGE) || 1

  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Online Visitors', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          <OptionsDrawerTrigger {...options.triggerProps} />
        </div>
      </div>

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer
        config={tableOptionsConfig}
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
      />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="online-visitors" />
        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load online visitors', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-32">
            <TableSkeleton rows={10} columns={7} />
          </PanelSkeleton>
        ) : (
          <DataTable
            tableRef={tableRef}
            columns={columns}
            data={visitors}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={total}
            rowLimit={PER_PAGE}
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            hiddenColumns={ONLINE_VISITORS_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            emptyStateMessage={__('No visitors are currently online', 'wp-statistics')}
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
