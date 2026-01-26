import { DataTable } from '@components/custom/data-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { ErrorMessage } from '@/components/custom/error-message'
import { DetailOptionsDrawer, useDetailOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  createSearchTermsColumns,
  transformSearchTermData,
} from '@/components/data-table-columns/search-terms-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { getSearchTermsQueryOptions } from '@/services/visitor-insight/get-search-terms'

export const Route = createLazyFileRoute('/(visitor-insights)/search-terms')({
  component: RouteComponent,
})

const PER_PAGE = 20

function RouteComponent() {
  const { page, handlePageChange, isInitialized, apiDateParams } = useGlobalFilters()

  // Create columns using shared factory
  const columns = useMemo(() => createSearchTermsColumns(), [])

  // Options drawer - config is passed once and returned for drawer
  const options = useDetailOptions({ filterGroup: 'visitors', hideFilters: true })

  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getSearchTermsQueryOptions({
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

  // Transform API data to component format
  const searchTerms = response?.data?.data?.rows?.map(transformSearchTermData) || []
  const total = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(total / PER_PAGE) || 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Search Terms', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
        showFilterButton={false}
      />

      {/* Options Drawer */}
      <DetailOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="search-terms" />
        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load search terms', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-28">
            <TableSkeleton rows={10} columns={2} />
          </PanelSkeleton>
        ) : (
          <DataTable
            columns={columns}
            data={searchTerms}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={total}
            rowLimit={PER_PAGE}
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            emptyStateMessage={__('No data available for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
