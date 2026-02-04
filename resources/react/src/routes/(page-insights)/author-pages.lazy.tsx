import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import {
  DetailOptionsDrawer,
  OptionsDrawerTrigger,
  useDetailOptions,
} from '@/components/custom/options-drawer'
import {
  createPageViewsColumns,
  createPageViewsTransform,
} from '@/components/data-table-columns/page-views-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { getAuthorPagesQueryOptions } from '@/services/page-insight/get-author-pages'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(page-insights)/author-pages')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    page,
    handleDateRangeUpdate,
    handlePageChange,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const columns = useMemo(
    () =>
      createPageViewsColumns({
        pageColumnHeader: __('Author', 'wp-statistics'),
        defaultTitle: __('Unknown Author', 'wp-statistics'),
        idPrefix: 'author',
      }),
    []
  )

  const transformData = useMemo(
    () =>
      createPageViewsTransform({
        defaultTitle: __('Unknown Author', 'wp-statistics'),
        idPrefix: 'author',
      }),
    []
  )

  // Options drawer with filter support
  const options = useDetailOptions({
    filterGroup: 'content',
  })

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getAuthorPagesQueryOptions({
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
    return extractRows(response).map(transformData)
  }, [response, transformData])

  const meta = extractMeta(response)
  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Author Pages', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
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
          <OptionsDrawerTrigger {...options.triggerProps} />
        </div>
      </div>

      {/* Options Drawer */}
      <DetailOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="author-pages" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load author pages', 'wp-statistics')} />
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
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={totalRows}
            rowLimit={PER_PAGE}
            showPagination={true}
            isFetching={isFetching}
            emptyStateMessage={__('No author pages found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
