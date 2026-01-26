import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import {
  DetailOptionsDrawer,
  OptionsDrawerTrigger,
  type PageFilterConfig,
  useDetailOptions,
} from '@/components/custom/options-drawer'
import { TaxonomySelect } from '@/components/custom/taxonomy-select'
import {
  createPageViewsColumns,
  createPageViewsTransform,
} from '@/components/data-table-columns/page-views-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useTaxonomyFilter } from '@/hooks/use-taxonomy-filter'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { getCategoryPagesQueryOptions } from '@/services/page-insight/get-category-pages'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(page-insights)/category-pages')({
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
    setPage,
    handleDateRangeUpdate,
    handlePageChange,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  // Taxonomy filter with URL sync
  const {
    value: taxonomyType,
    onChange: baseTaxonomyChange,
    pageFilterConfig: taxonomyFilterConfig,
  } = useTaxonomyFilter()

  // Wrap taxonomy change to also reset page
  const handleTaxonomyChange = useCallback(
    (value: string) => {
      baseTaxonomyChange(value)
      setPage(1) // Reset to first page when taxonomy changes
    },
    [baseTaxonomyChange, setPage]
  )

  // Page filters config for Options drawer - with page reset on change
  const pageFilters = useMemo<PageFilterConfig[]>(
    () => [{ ...taxonomyFilterConfig, onChange: handleTaxonomyChange }],
    [taxonomyFilterConfig, handleTaxonomyChange]
  )

  const columns = useMemo(
    () =>
      createPageViewsColumns({
        pageColumnHeader: __('Term Page', 'wp-statistics'),
        defaultTitle: __('Unknown', 'wp-statistics'),
        idPrefix: 'category',
      }),
    []
  )

  const transformData = useMemo(
    () =>
      createPageViewsTransform({
        defaultTitle: __('Unknown', 'wp-statistics'),
        idPrefix: 'category',
      }),
    []
  )

  // Options drawer - config is passed once and returned for drawer
  const options = useDetailOptions({
    filterGroup: 'categories',
    hideFilters: true,
    pageFilters,
  })

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getCategoryPagesQueryOptions({
      page,
      per_page: PER_PAGE,
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      taxonomyType,
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
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Category Pages', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          <TaxonomySelect value={taxonomyType} onValueChange={handleTaxonomyChange} />
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
        <NoticeContainer className="mb-2" currentRoute="category-pages" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load category pages', 'wp-statistics')} />
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
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            emptyStateMessage={__('No term pages found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
