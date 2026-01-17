import type { Table } from '@tanstack/react-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import {
  OptionsDrawerTrigger,
  TableOptionsDrawer,
  useTableOptions,
} from '@/components/custom/options-drawer'
import {
  createTopCategoriesColumns,
  type TopCategory,
  TOP_CATEGORIES_COLUMN_CONFIG,
  TOP_CATEGORIES_CONTEXT,
  TOP_CATEGORIES_DEFAULT_API_COLUMNS,
  TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
  transformTopCategoryData,
} from '@/components/data-table-columns/top-categories-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { WordPress } from '@/lib/wordpress'
import { getTopCategoriesQueryOptions } from '@/services/content-analytics/get-top-categories'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(content-analytics)/top-categories')({
  component: RouteComponent,
})

function RouteComponent() {
  const { taxonomy } = Route.useSearch()

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
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'views', desc: true }],
    onPageReset: () => setPage(1),
  })
  const [defaultFilterRemoved, setDefaultFilterRemoved] = useState(false)

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<TopCategory> | null>(null)

  const wp = WordPress.getInstance()
  const columns = useMemo(() => createTopCategoriesColumns(), [])

  // Available filters: Author, Post Type, Cached Date, Taxonomy Type
  const ADVANCED_FILTERS = ['author', 'post_type', 'cached_date']
  const ALL_VALID_FILTERS = [...ADVANCED_FILTERS, 'taxonomy_type']

  const filterFields = useMemo<FilterField[]>(() => {
    return wp
      .getFilterFieldsByGroup('categories')
      .filter((field) => ALL_VALID_FILTERS.includes(field.name)) as FilterField[]
  }, [wp])

  // Don't inherit filters from global state - this page starts fresh
  const [localFilters, setLocalFilters] = useState<typeof appliedFilters>([])

  // Use local filters instead of global filters for this page
  const normalizedFilters = useMemo(() => {
    return (localFilters || []).filter((f) => {
      const filterName = f.id.split('-')[0]
      return ALL_VALID_FILTERS.includes(filterName)
    })
  }, [localFilters])

  // Check if user has applied a taxonomy_type filter (overriding default)
  const hasUserTaxonomyFilter = normalizedFilters.some((f) => f.id.startsWith('taxonomy_type'))

  // Build default taxonomy_type filter
  const defaultTaxonomyFilter = useMemo(() => {
    const taxonomyField = filterFields.find((f) => f.name === 'taxonomy_type')
    const defaultTaxonomyType = taxonomy || 'category'
    const taxonomyOption = taxonomyField?.options?.find((o) => o.value === defaultTaxonomyType)
    return {
      id: 'taxonomy_type-top-categories-default',
      label: taxonomyField?.label || __('Taxonomy Type', 'wp-statistics'),
      operator: '=',
      rawOperator: 'is',
      value: taxonomyOption?.label || defaultTaxonomyType,
      rawValue: defaultTaxonomyType,
    }
  }, [filterFields, taxonomy])

  // Determine if we should show the default filter
  const showDefaultFilter = !hasUserTaxonomyFilter && !defaultFilterRemoved

  // Filters to use for API requests and display
  const filtersForApi = useMemo(() => {
    return showDefaultFilter ? [...normalizedFilters, defaultTaxonomyFilter] : normalizedFilters
  }, [showDefaultFilter, normalizedFilters, defaultTaxonomyFilter])

  const filtersForDisplay = filtersForApi

  // Handle filter application
  const handleTopCategoriesApplyFilters = useCallback(
    (newFilters: typeof appliedFilters) => {
      const hasTaxonomyFilter = (newFilters || []).some((f) => f.id.startsWith('taxonomy_type'))
      if (hasTaxonomyFilter) {
        setDefaultFilterRemoved(false)
      }
      setLocalFilters(newFilters)
      setPage(1)
    },
    [setPage]
  )

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Get the current taxonomy type for API request
  const currentTaxonomyType = useMemo(() => {
    const userTaxonomyFilter = filtersForApi.find((f) => f.id.startsWith('taxonomy_type'))
    return (userTaxonomyFilter?.rawValue as string) || (defaultTaxonomyFilter.rawValue as string)
  }, [filtersForApi, defaultTaxonomyFilter])

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getTopCategoriesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: filtersForApi,
      context: TOP_CATEGORIES_CONTEXT,
      columns: TOP_CATEGORIES_DEFAULT_API_COLUMNS,
      taxonomyType: currentTaxonomyType,
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
    context: TOP_CATEGORIES_CONTEXT,
    columns,
    defaultHiddenColumns: TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: TOP_CATEGORIES_DEFAULT_API_COLUMNS,
    columnConfig: TOP_CATEGORIES_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'views',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
  })

  // Options drawer with column management
  const options = useTableOptions({
    filterGroup: 'categories',
    table: tableRef.current,
    defaultHiddenColumns: TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onReset: handleColumnPreferencesReset,
  })

  // Transform API data to component interface
  const tableData = useMemo(() => {
    return extractRows(response).map(transformTopCategoryData)
  }, [response])

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

  // Get the display title based on current taxonomy
  const pageTitle = useMemo(() => {
    const taxonomyLabels: Record<string, string> = {
      category: __('Top Categories', 'wp-statistics'),
      post_tag: __('Top Tags', 'wp-statistics'),
    }
    return taxonomyLabels[currentTaxonomyType] || __('Top Terms', 'wp-statistics')
  }, [currentTaxonomyType])

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{pageTitle}</h1>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={filtersForDisplay}
                onApplyFilters={handleTopCategoriesApplyFilters}
                filterGroup="categories"
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
          <OptionsDrawerTrigger {...options.triggerProps} />
        </div>
      </div>

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer
        config={{
          filterGroup: 'categories',
          table: tableRef.current,
          defaultHiddenColumns: TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
          onColumnVisibilityChange: handleColumnVisibilityChange,
          onColumnOrderChange: handleColumnOrderChange,
          onReset: handleColumnPreferencesReset,
        }}
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
      />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="top-categories" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load top categories', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={6} />
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
            hiddenColumns={TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            emptyStateMessage={__('No terms found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
