import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import {
  DetailOptionsDrawer,
  OptionsDrawerTrigger,
  type PageFilterConfig,
  useDetailOptions,
} from '@/components/custom/options-drawer'
import {
  createPageViewsColumns,
  createPageViewsTransform,
} from '@/components/data-table-columns/page-views-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { WordPress } from '@/lib/wordpress'
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
    setDateRange,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const [taxonomyType, setTaxonomyType] = useState('category')
  const wp = WordPress.getInstance()

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

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  const handleTaxonomyChange = useCallback(
    (value: string) => {
      setTaxonomyType(value)
      setPage(1)
    },
    [setPage]
  )

  // Page filters config for Options drawer
  const pageFilters = useMemo<PageFilterConfig[]>(() => {
    const taxonomies = wp.getTaxonomies()
    return [
      {
        id: 'taxonomy',
        label: __('Taxonomy Type', 'wp-statistics'),
        value: taxonomyType,
        options: taxonomies,
        onChange: handleTaxonomyChange,
      },
    ]
  }, [taxonomyType, handleTaxonomyChange, wp])

  // Options drawer
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
    if (!response?.data?.data?.rows) return []
    return response.data.data.rows.map(transformData)
  }, [response, transformData])

  const totalRows = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

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
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Category Pages', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          <Select value={taxonomyType} onValueChange={handleTaxonomyChange}>
            <SelectTrigger className="h-8 w-auto text-xs gap-1 px-3 border-neutral-200 hover:bg-neutral-50">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {wp.getTaxonomies().map((taxonomy) => (
                <SelectItem key={taxonomy.value} value={taxonomy.value}>
                  {taxonomy.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
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
      <DetailOptionsDrawer
        config={{
          filterGroup: 'categories',
          hideFilters: true,
          pageFilters,
        }}
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
      />

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
