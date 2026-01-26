import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import { DetailOptionsDrawer, useDetailOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  createNotFoundPagesColumns,
  transformNotFoundPageData,
} from '@/components/data-table-columns/404-pages-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { get404PagesQueryOptions, type NotFoundPageRecord } from '@/services/page-insight/get-404-pages'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(page-insights)/404-pages')({
  component: RouteComponent,
})

function RouteComponent() {
  const { page, handlePageChange, isInitialized, apiDateParams } = useGlobalFilters()

  // Static sorting - always views descending
  const sorting = useMemo(() => [{ id: 'views', desc: true }], [])

  const columns = useMemo(() => createNotFoundPagesColumns(), [])

  // Options drawer - config is passed once and returned for drawer
  const options = useDetailOptions({ filterGroup: 'views', hideFilters: true })

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...get404PagesQueryOptions({
      page,
      per_page: PER_PAGE,
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Transform API data to component interface
  const tableData = useMemo(() => {
    return extractRows<NotFoundPageRecord>(response).map(transformNotFoundPageData)
  }, [response])

  const meta = extractMeta(response)
  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('404 Pages', 'wp-statistics')}
        filterGroup="views"
        optionsTriggerProps={options.triggerProps}
        showFilterButton={false}
      />

      {/* Options Drawer */}
      <DetailOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="404-pages" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load 404 pages', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={2} />
          </PanelSkeleton>
        ) : (
          <DataTable
            columns={columns}
            data={tableData}
            sorting={sorting}
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
            emptyStateMessage={__('No 404 pages found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
